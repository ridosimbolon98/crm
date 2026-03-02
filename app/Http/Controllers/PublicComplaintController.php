<?php

namespace App\Http\Controllers;

use App\Http\Requests\Complaint\StorePublicComplaintRequest;
use App\Models\Brand;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\ComplaintEventNotification;
use App\Services\AuditLogger;
use App\Services\ComplaintNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicComplaintController extends Controller
{
    public function __construct(private readonly ComplaintNotificationService $complaintNotificationService)
    {
    }

    public function landing(Request $request): View|RedirectResponse
    {
        if ($request->user()) {
            return redirect()->route('dashboard.index');
        }

        return $this->create();
    }

    public function create(): View
    {
        return view('public.complaint-form', [
            'brands' => Brand::query()->orderBy('name')->get(),
            'categories' => ComplaintCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StorePublicComplaintRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $customer = Customer::query()
            ->where(function ($query) use ($validated) {
                $query->where('phone', $validated['phone']);
                if (! empty($validated['email'])) {
                    $query->orWhere('email', $validated['email']);
                }
            })
            ->first();

        if (! $customer) {
            $customer = Customer::query()->create([
                'name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'city' => $validated['city'] ?? null,
                'address' => $validated['address'] ?? null,
                'is_active' => true,
            ]);
        }

        $complaint = Complaint::query()->create([
            'ticket_number' => $this->generateTicketNumber(),
            'source' => 'public_web',
            'customer_id' => $customer->id,
            'customer_name' => $validated['full_name'],
            'customer_phone' => $validated['phone'],
            'customer_email' => $validated['email'] ?? null,
            'brand_id' => $validated['brand_id'] ?? null,
            'complaint_category_id' => $validated['complaint_category_id'] ?? null,
            'complaint_channel' => 'Website Form',
            'production_code' => $validated['production_code'] ?? null,
            'complaint_date' => now()->toDateString(),
            'severity' => 'Medium',
            'status' => 'Open',
            'description' => $validated['story'],
            'current_pool_department' => User::DEPT_QA,
            'extra_payload' => [
                'gender' => $validated['gender'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'province' => $validated['province'] ?? null,
                'city' => $validated['city'] ?? null,
                'district' => $validated['district'] ?? null,
                'subdistrict' => $validated['subdistrict'] ?? null,
                'address' => $validated['address'] ?? null,
                'purchase_date' => $validated['purchase_date'] ?? null,
                'purchase_location' => $validated['purchase_location'] ?? null,
                'consent' => true,
            ],
            'capa_status' => Complaint::CAPA_STATUS_DRAFT,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store("complaints/{$complaint->id}", 'public');
                $complaint->attachments()->create([
                    'disk' => 'public',
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize() ?: 0,
                    'uploaded_by' => 'Customer',
                ]);
            }
        }

        $complaint->updates()->create([
            'event_type' => 'created',
            'status_after' => $complaint->status,
            'author' => 'Customer',
            'department' => User::DEPT_GENERAL,
            'pool_to_department' => User::DEPT_QA,
            'note' => 'Complaint dibuat melalui halaman publik.',
            'event_at' => now(),
        ]);

        AuditLogger::log($request, 'complaint.public_created', $complaint, ['source' => 'public_web']);

        $users = User::query()
            ->where('is_active', true)
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_MANAGER, User::ROLE_QA, User::ROLE_CS])
            ->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new ComplaintEventNotification(
                complaint: $complaint,
                event: 'public_created',
                message: 'Complaint baru masuk dari customer (form publik).',
            ));
        }

        $this->complaintNotificationService->notifyConfiguredRecipientsForIncomingComplaint($complaint);

        return redirect()
            ->route('public.complaints.create')
            ->with('success', 'Terima kasih. Keluhan Anda berhasil dikirim dengan nomor tiket '.$complaint->ticket_number.'.');
    }

    private function generateTicketNumber(): string
    {
        do {
            $ticketNumber = 'CMP-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (Complaint::query()->where('ticket_number', $ticketNumber)->exists());

        return $ticketNumber;
    }
}
