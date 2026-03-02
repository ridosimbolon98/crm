<?php

namespace App\Http\Controllers;

use App\Http\Requests\Complaint\StoreComplaintAttachmentRequest;
use App\Http\Requests\Complaint\StoreComplaintNoteRequest;
use App\Http\Requests\Complaint\StoreReplacementProgressRequest;
use App\Http\Requests\Complaint\StoreComplaintRequest;
use App\Http\Requests\Complaint\UpdateComplaintStatusRequest;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Complaint;
use App\Models\ComplaintAttachment;
use App\Models\ComplaintCategory;
use App\Models\ComplaintSeverity;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\ComplaintEventNotification;
use App\Services\AuditLogger;
use App\Services\ComplaintNotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComplaintController extends Controller
{
    public function __construct(private readonly ComplaintNotificationService $complaintNotificationService)
    {
    }

    public function index(Request $request): View
    {
        $filters = $this->extractFilters($request);

        $complaints = $this->applyFilters(Complaint::query()->with(['brand', 'category']), $filters)
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        $now = now();
        $summary = [
            'total' => Complaint::count(),
            'open' => Complaint::whereIn('status', ['Open', 'Investigating', 'Action Plan'])->count(),
            'resolved_today' => Complaint::whereDate('closed_at', $now->toDateString())->count(),
            'overdue' => Complaint::whereNotIn('status', ['Resolved', 'Closed'])
                ->whereDate('target_resolution_date', '<', $now->toDateString())
                ->count(),
        ];

        return view('complaints.index', [
            'complaints' => $complaints,
            'summary' => $summary,
            'filters' => $filters,
            'canCreate' => $request->user()?->hasRole(User::ROLE_ADMIN, User::ROLE_MANAGER, User::ROLE_QA, User::ROLE_CS, User::ROLE_SALES, User::ROLE_MARKETING) ?? false,
            'canExport' => $request->user()?->hasRole(User::ROLE_ADMIN, User::ROLE_MANAGER, User::ROLE_QA, User::ROLE_VIEWER, User::ROLE_CS) ?? false,
            'statusOptions' => Complaint::STATUS_OPTIONS,
            'severityOptions' => ComplaintSeverity::query()->where('is_active', true)->orderBy('sort_order')->pluck('name')->values(),
            'brands' => Brand::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('complaints.create', [
            'customers' => Customer::query()->where('is_active', true)->orderBy('name')->get(),
            'brands' => Brand::query()->orderBy('name')->get(),
            'categories' => ComplaintCategory::query()->orderBy('name')->get(),
            'statusOptions' => Complaint::STATUS_OPTIONS,
            'severityOptions' => ComplaintSeverity::query()->where('is_active', true)->orderBy('sort_order')->pluck('name')->values(),
            'channelOptions' => Complaint::CHANNEL_OPTIONS,
        ]);
    }

    public function show(Request $request, Complaint $complaint): View
    {
        $complaint->load(['brand', 'category', 'customer', 'updates', 'attachments', 'capaApprover', 'replacementProgresses.user']);

        $user = $request->user();
        $canUpdateWorkflow = $user ? $this->canUserWorkOnComplaint($user, $complaint) : false;
        $canManageActionType = $user?->hasRole(User::ROLE_ADMIN, User::ROLE_QA) ?? false;
        $canViewReplacementProgress = $user?->hasRole(User::ROLE_ADMIN, User::ROLE_QA) || $user?->department === User::DEPT_PPIC;
        $canUpdateReplacementProgress = $user?->department === User::DEPT_PPIC;

        $auditLogs = AuditLog::query()
            ->with('user')
            ->where('auditable_type', Complaint::class)
            ->where('auditable_id', $complaint->id)
            ->orderByDesc('created_at')
            ->limit(25)
            ->get();

        return view('complaints.show', [
            'complaint' => $complaint,
            'auditLogs' => $auditLogs,
            'statusOptions' => Complaint::STATUS_OPTIONS,
            'departmentOptions' => User::DEPARTMENT_OPTIONS,
            'canEdit' => $canUpdateWorkflow,
            'canUpdateWorkflow' => $canUpdateWorkflow,
            'canManageActionType' => $canManageActionType,
            'canViewReplacementProgress' => $canViewReplacementProgress,
            'canUpdateReplacementProgress' => $canUpdateReplacementProgress,
            'canUpdateStatus' => $request->user()?->hasRole(User::ROLE_ADMIN, User::ROLE_MANAGER, User::ROLE_QA) ?? false,
            'canSubmitCapa' => $request->user()?->hasRole(User::ROLE_ADMIN, User::ROLE_QA) ?? false,
            'canApproveCapa' => $request->user()?->hasRole(User::ROLE_ADMIN, User::ROLE_MANAGER) ?? false,
            'canCloseCapa' => $request->user()?->hasRole(User::ROLE_ADMIN, User::ROLE_MANAGER, User::ROLE_QA) ?? false,
            'extraPayload' => $complaint->extra_payload ?? [],
        ]);
    }

    public function store(StoreComplaintRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['ticket_number'] = $this->generateTicketNumber();
        $validated['capa_status'] = Complaint::CAPA_STATUS_DRAFT;
        $validated['current_pool_department'] = User::DEPT_QA;

        unset($validated['attachments']);

        if (! empty($validated['customer_id'])) {
            $customer = Customer::query()->find($validated['customer_id']);
            if ($customer) {
                $validated['customer_name'] = $customer->name;
                $validated['customer_phone'] = $customer->phone;
                $validated['customer_email'] = $customer->email;
            }
        }

        $complaint = Complaint::create($validated);

        $complaint->updates()->create([
            'event_type' => 'created',
            'user_id' => $request->user()?->id,
            'status_after' => $complaint->status,
            'author' => $request->string('author')->toString() ?: $request->user()?->name ?: 'System',
            'department' => $request->user()?->department ?? User::DEPT_GENERAL,
            'pool_to_department' => User::DEPT_QA,
            'note' => 'Tiket dibuat dan menunggu tindak lanjut.',
            'event_at' => now(),
        ]);

        if ($request->hasFile('attachments')) {
            $this->saveAttachments(
                complaint: $complaint,
                files: $request->file('attachments'),
                author: $request->user()?->name ?: 'System',
            );
        }

        AuditLogger::log($request, 'complaint.created', $complaint, [
            'ticket_number' => $complaint->ticket_number,
            'status' => $complaint->status,
        ]);

        $this->notifyStakeholders($complaint, 'created', 'Tiket complaint baru dibuat.');
        $this->complaintNotificationService->notifyConfiguredRecipientsForIncomingComplaint($complaint);

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', 'Tiket keluhan berhasil dibuat.');
    }

    public function updateStatus(UpdateComplaintStatusRequest $request, Complaint $complaint): RedirectResponse
    {
        $this->abortIfUnauthorizedWorkflowUpdate($request, $complaint);

        $validated = $request->validated();

        $statusBefore = $complaint->status;
        $poolBefore = $complaint->current_pool_department;
        $poolToDepartment = $validated['pool_to_department'] ?? $complaint->current_pool_department;

        $complaint->fill([
            'status' => $validated['status'],
            'assigned_to' => $validated['assigned_to'] ?? $complaint->assigned_to,
            'target_resolution_date' => $validated['target_resolution_date'] ?? $complaint->target_resolution_date,
            'resolution_summary' => $validated['resolution_summary'] ?? $complaint->resolution_summary,
            'compensation_type' => $validated['compensation_type'] ?? $complaint->compensation_type,
            'current_pool_department' => $poolToDepartment,
            'closed_at' => $validated['status'] === 'Closed' ? Carbon::now() : null,
        ])->save();

        $complaint->updates()->create([
            'event_type' => 'workflow_progress',
            'user_id' => $request->user()?->id,
            'status_before' => $statusBefore,
            'status_after' => $validated['status'],
            'author' => $request->user()?->name ?? ($validated['author'] ?? 'System'),
            'department' => $request->user()?->department ?? User::DEPT_GENERAL,
            'pool_to_department' => $poolToDepartment,
            'note' => $validated['detail_progress'],
            'event_at' => now(),
        ]);

        AuditLogger::log($request, 'complaint.status_updated', $complaint, [
            'before' => $statusBefore,
            'after' => $validated['status'],
        ]);

        $this->notifyStakeholders(
            $complaint,
            'status_updated',
            "Status berubah dari {$statusBefore} menjadi {$validated['status']}. Progress: {$validated['detail_progress']}",
        );

        if ($poolToDepartment !== $poolBefore) {
            $this->complaintNotificationService->notifyDepartmentUsers(
                $complaint,
                $poolToDepartment,
                'department_pooled',
                "Anda menerima pool ticket {$complaint->ticket_number} untuk ditindaklanjuti."
            );
        }

        if ($validated['status'] === 'Closed' && $statusBefore !== 'Closed') {
            $this->complaintNotificationService->notifyMarketingOnClosed($complaint);
        }

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', 'Progress workflow berhasil diperbarui.');
    }

    public function submitCapa(Request $request, Complaint $complaint): RedirectResponse
    {
        $validated = $request->validate([
            'capa_root_cause' => ['required', 'string', 'max:5000'],
            'capa_corrective_action' => ['required', 'string', 'max:5000'],
            'capa_preventive_action' => ['required', 'string', 'max:5000'],
            'capa_due_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:5000'],
        ]);

        $complaint->fill([
            'capa_root_cause' => $validated['capa_root_cause'],
            'capa_corrective_action' => $validated['capa_corrective_action'],
            'capa_preventive_action' => $validated['capa_preventive_action'],
            'capa_due_date' => $validated['capa_due_date'],
            'capa_status' => Complaint::CAPA_STATUS_SUBMITTED,
            'capa_submitted_at' => now(),
            'capa_rejected_reason' => null,
        ])->save();

        $complaint->updates()->create([
            'event_type' => 'capa_submitted',
            'status_after' => $complaint->status,
            'author' => $request->user()?->name ?? 'QA',
            'note' => $validated['note'] ?? 'Dokumen CAPA disubmit untuk approval manager.',
            'event_at' => now(),
        ]);

        AuditLogger::log($request, 'complaint.capa_submitted', $complaint, [
            'due_date' => $validated['capa_due_date'],
        ]);

        $this->notifyStakeholders($complaint, 'capa_submitted', 'CAPA telah disubmit oleh tim QA.');

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', 'CAPA berhasil disubmit untuk approval manager.');
    }

    public function approveCapa(Request $request, Complaint $complaint): RedirectResponse
    {
        if ($complaint->capa_status !== Complaint::CAPA_STATUS_SUBMITTED) {
            return back()->withErrors(['capa' => 'CAPA hanya bisa di-approve ketika status Submitted.']);
        }

        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:5000'],
        ]);

        $complaint->fill([
            'capa_status' => Complaint::CAPA_STATUS_APPROVED,
            'capa_approved_at' => now(),
            'capa_approved_by' => $request->user()?->id,
            'capa_rejected_reason' => null,
        ])->save();

        $complaint->updates()->create([
            'event_type' => 'capa_approved',
            'status_after' => $complaint->status,
            'author' => $request->user()?->name ?? 'Manager',
            'note' => $validated['note'] ?? 'CAPA disetujui manager.',
            'event_at' => now(),
        ]);

        AuditLogger::log($request, 'complaint.capa_approved', $complaint);

        $this->notifyStakeholders($complaint, 'capa_approved', 'CAPA telah di-approve manager.');

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', 'CAPA berhasil di-approve.');
    }

    public function rejectCapa(Request $request, Complaint $complaint): RedirectResponse
    {
        if (! in_array($complaint->capa_status, [Complaint::CAPA_STATUS_SUBMITTED, Complaint::CAPA_STATUS_APPROVED], true)) {
            return back()->withErrors(['capa' => 'CAPA tidak berada pada status yang bisa ditolak.']);
        }

        $validated = $request->validate([
            'capa_rejected_reason' => ['required', 'string', 'max:5000'],
        ]);

        $complaint->fill([
            'capa_status' => Complaint::CAPA_STATUS_REJECTED,
            'capa_rejected_reason' => $validated['capa_rejected_reason'],
            'capa_approved_by' => null,
            'capa_approved_at' => null,
        ])->save();

        $complaint->updates()->create([
            'event_type' => 'capa_rejected',
            'status_after' => $complaint->status,
            'author' => $request->user()?->name ?? 'Manager',
            'note' => $validated['capa_rejected_reason'],
            'event_at' => now(),
        ]);

        AuditLogger::log($request, 'complaint.capa_rejected', $complaint, [
            'reason' => $validated['capa_rejected_reason'],
        ]);

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', 'CAPA ditolak dan dikembalikan ke QA.');
    }

    public function closeCapa(Request $request, Complaint $complaint): RedirectResponse
    {
        if ($complaint->capa_status !== Complaint::CAPA_STATUS_APPROVED) {
            return back()->withErrors(['capa' => 'Tiket hanya bisa ditutup jika CAPA sudah Approved.']);
        }

        $validated = $request->validate([
            'resolution_summary' => ['required', 'string', 'max:5000'],
        ]);

        $statusBefore = $complaint->status;

        $complaint->fill([
            'status' => 'Closed',
            'closed_at' => now(),
            'resolution_summary' => $validated['resolution_summary'],
            'capa_status' => Complaint::CAPA_STATUS_CLOSED,
        ])->save();

        $complaint->updates()->create([
            'event_type' => 'ticket_closed',
            'status_before' => $statusBefore,
            'status_after' => 'Closed',
            'author' => $request->user()?->name ?? 'System',
            'note' => 'Tiket ditutup setelah CAPA approval.',
            'event_at' => now(),
        ]);

        AuditLogger::log($request, 'complaint.closed', $complaint, ['status_before' => $statusBefore]);

        $this->notifyStakeholders($complaint, 'closed', 'Tiket complaint telah ditutup.');
        $this->complaintNotificationService->notifyMarketingOnClosed($complaint);

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', 'Tiket berhasil ditutup sesuai workflow CAPA.');
    }

    public function storeNote(StoreComplaintNoteRequest $request, Complaint $complaint): RedirectResponse
    {
        $this->abortIfUnauthorizedWorkflowUpdate($request, $complaint);

        $validated = $request->validated();

        $complaint->updates()->create([
            'event_type' => 'note',
            'user_id' => $request->user()?->id,
            'status_after' => $complaint->status,
            'author' => $validated['author'],
            'department' => $request->user()?->department ?? User::DEPT_GENERAL,
            'pool_to_department' => $complaint->current_pool_department,
            'note' => $validated['note'],
            'event_at' => now(),
        ]);

        AuditLogger::log($request, 'complaint.note_added', $complaint);

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', 'Catatan investigasi berhasil ditambahkan.');
    }

    public function storeAttachment(StoreComplaintAttachmentRequest $request, Complaint $complaint): RedirectResponse
    {
        $this->abortIfUnauthorizedWorkflowUpdate($request, $complaint);

        $this->saveAttachments(
            complaint: $complaint,
            files: $request->file('files'),
            author: $request->input('author') ?: $request->user()?->name,
        );

        $complaint->updates()->create([
            'event_type' => 'attachment',
            'user_id' => $request->user()?->id,
            'status_after' => $complaint->status,
            'author' => $request->input('author') ?: $request->user()?->name ?: 'System',
            'department' => $request->user()?->department ?? User::DEPT_GENERAL,
            'pool_to_department' => $complaint->current_pool_department,
            'note' => 'Bukti complaint ditambahkan.',
            'event_at' => now(),
        ]);

        AuditLogger::log($request, 'complaint.attachment_uploaded', $complaint, [
            'files_count' => count($request->file('files')),
        ]);

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', 'Lampiran berhasil diunggah.');
    }

    public function downloadAttachment(Request $request, Complaint $complaint, ComplaintAttachment $attachment): StreamedResponse
    {
        abort_if($attachment->complaint_id !== $complaint->id, 404);

        AuditLogger::log($request, 'complaint.attachment_downloaded', $complaint, [
            'attachment_id' => $attachment->id,
            'filename' => $attachment->original_name,
        ]);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $filters = $this->extractFilters($request);

        $rows = $this->applyFilters(Complaint::query()->with(['brand', 'category']), $filters)
            ->latest('created_at')
            ->get();

        $filename = 'complaints-'.now()->format('Ymd-His').'.csv';

        AuditLogger::log($request, 'complaint.export_excel', null, $filters);

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, [
                'Ticket',
                'Customer',
                'Brand',
                'Category',
                'Date',
                'Severity',
                'Status',
                'CAPA',
                'PIC',
                'Target Resolution',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->ticket_number,
                    $row->customer_name,
                    $row->brand?->name,
                    $row->category?->name,
                    optional($row->complaint_date)->format('Y-m-d'),
                    $row->severity,
                    $row->status,
                    $row->capa_status,
                    $row->assigned_to,
                    optional($row->target_resolution_date)->format('Y-m-d'),
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdf(Request $request): View
    {
        $filters = $this->extractFilters($request);

        $rows = $this->applyFilters(Complaint::query()->with(['brand', 'category']), $filters)
            ->latest('created_at')
            ->get();

        AuditLogger::log($request, 'complaint.export_pdf', null, $filters);

        return view('complaints.pdf', [
            'rows' => $rows,
            'filters' => $filters,
            'generatedAt' => now(),
        ]);
    }

    public function updateActionType(Request $request, Complaint $complaint): RedirectResponse
    {
        abort_unless($request->user()?->hasRole(User::ROLE_ADMIN, User::ROLE_QA), 403);

        $validated = $request->validate([
            'action_type' => ['required', 'in:'.implode(',', Complaint::ACTION_TYPE_OPTIONS)],
        ]);

        $complaint->update([
            'action_type' => $validated['action_type'],
        ]);

        $complaint->updates()->create([
            'event_type' => 'action_type_updated',
            'user_id' => $request->user()?->id,
            'status_after' => $complaint->status,
            'author' => $request->user()?->name ?? 'QA',
            'department' => $request->user()?->department ?? User::DEPT_GENERAL,
            'pool_to_department' => $complaint->current_pool_department,
            'note' => 'Action type diubah menjadi '.$validated['action_type'],
            'event_at' => now(),
        ]);

        AuditLogger::log($request, 'complaint.action_type_updated', $complaint, $validated);

        if ($validated['action_type'] === Complaint::ACTION_TYPE_REPLACE_PRODUCT) {
            $this->complaintNotificationService->notifyDepartmentUsers(
                $complaint,
                User::DEPT_PPIC,
                'replacement_required',
                "Complaint {$complaint->ticket_number} membutuhkan proses penggantian barang."
            );
        }

        return back()->with('success', 'Action type complaint berhasil diperbarui.');
    }

    public function storeReplacementProgress(StoreReplacementProgressRequest $request, Complaint $complaint): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->department === User::DEPT_PPIC || $user?->hasRole(User::ROLE_ADMIN, User::ROLE_QA), 403);

        $validated = $request->validated();

        $complaint->replacementProgresses()->create([
            'user_id' => $user?->id,
            'department' => $user?->department ?? User::DEPT_GENERAL,
            'item_name' => $validated['item_name'],
            'quantity' => (int) $validated['quantity'],
            'delivery_note_number' => $validated['delivery_note_number'],
            'note' => $validated['note'] ?? null,
            'event_at' => now(),
        ]);

        $complaint->updates()->create([
            'event_type' => 'replacement_progress',
            'user_id' => $user?->id,
            'status_after' => $complaint->status,
            'author' => $user?->name ?? 'PPIC',
            'department' => $user?->department ?? User::DEPT_PPIC,
            'pool_to_department' => $complaint->current_pool_department,
            'note' => "Progress penggantian barang: {$validated['item_name']} qty {$validated['quantity']}, Surat Jalan {$validated['delivery_note_number']}. ".($validated['note'] ?? ''),
            'event_at' => now(),
        ]);

        AuditLogger::log($request, 'complaint.replacement_progress_added', $complaint, [
            'item_name' => $validated['item_name'],
            'quantity' => (int) $validated['quantity'],
            'delivery_note_number' => $validated['delivery_note_number'],
        ]);

        return back()->with('success', 'Progress penggantian barang berhasil ditambahkan.');
    }

    private function saveAttachments(Complaint $complaint, array $files, ?string $author): void
    {
        foreach ($files as $file) {
            $path = $file->store("complaints/{$complaint->id}", 'public');

            $complaint->attachments()->create([
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize() ?: 0,
                'uploaded_by' => $author ?: 'System',
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function extractFilters(Request $request): array
    {
        return $request->only(['search', 'status', 'severity', 'brand', 'from', 'to']);
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                $filters['search'] ?? null,
                fn ($q, $search) => $q->where(function ($inner) use ($search) {
                    $inner
                        ->where('ticket_number', 'like', '%'.$search.'%')
                        ->orWhere('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('production_code', 'like', '%'.$search.'%');
                })
            )
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['severity'] ?? null, fn ($q, $severity) => $q->where('severity', $severity))
            ->when($filters['brand'] ?? null, fn ($q, $brandId) => $q->where('brand_id', $brandId))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('complaint_date', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('complaint_date', '<=', $to));
    }

    private function notifyStakeholders(Complaint $complaint, string $event, string $message): void
    {
        $users = User::query()
            ->where('is_active', true)
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_MANAGER, User::ROLE_QA])
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new ComplaintEventNotification(
            complaint: $complaint,
            event: $event,
            message: $message,
        ));
    }

    private function generateTicketNumber(): string
    {
        do {
            $ticketNumber = 'CMP-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (Complaint::query()->where('ticket_number', $ticketNumber)->exists());

        return $ticketNumber;
    }

    private function canUserWorkOnComplaint(User $user, Complaint $complaint): bool
    {
        if ($complaint->status === 'Closed') {
            return false;
        }

        if ($user->hasRole(User::ROLE_ADMIN, User::ROLE_QA)) {
            return true;
        }

        return ! empty($complaint->current_pool_department)
            && $user->department === $complaint->current_pool_department;
    }

    private function abortIfUnauthorizedWorkflowUpdate(Request $request, Complaint $complaint): void
    {
        $user = $request->user();
        abort_unless($user && $this->canUserWorkOnComplaint($user, $complaint), 403, 'Ticket belum dipool ke departemen Anda.');
    }
}
