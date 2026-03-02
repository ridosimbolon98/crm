<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\ComplaintCategory;
use App\Models\ComplaintSeverity;
use App\Models\Customer;
use App\Models\NotificationRecipient;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    public function index(): View
    {
        $activeTab = request()->string('tab')->toString() ?: 'brand';

        return view('master-data.index', [
            'brands' => Brand::query()->orderBy('name')->get(),
            'categories' => ComplaintCategory::query()->orderBy('name')->get(),
            'severities' => ComplaintSeverity::query()->orderBy('sort_order')->orderBy('name')->get(),
            'customers' => Customer::query()->orderByDesc('created_at')->paginate(15)->withQueryString(),
            'notificationRecipients' => NotificationRecipient::query()->orderByDesc('created_at')->get(),
            'activeTab' => $activeTab,
        ]);
    }

    public function storeBrand(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:brands,name'],
            'code' => ['required', 'string', 'max:16', 'unique:brands,code'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $brand = Brand::query()->create($validated);
        AuditLogger::log($request, 'master.brand.created', $brand);

        return back()->with('success', 'Master brand berhasil ditambahkan.');
    }

    public function updateBrand(Request $request, Brand $brand): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:brands,name,'.$brand->id],
            'code' => ['required', 'string', 'max:16', 'unique:brands,code,'.$brand->id],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $brand->update($validated);
        AuditLogger::log($request, 'master.brand.updated', $brand);

        return back()->with('success', 'Master brand berhasil diperbarui.');
    }

    public function deleteBrand(Request $request, Brand $brand): RedirectResponse
    {
        $brand->delete();
        AuditLogger::log($request, 'master.brand.deleted', null, ['brand_id' => $brand->id]);

        return back()->with('success', 'Master brand berhasil dihapus.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:complaint_categories,name'],
            'sla_label' => ['nullable', 'string', 'max:40'],
            'target_resolution_hours' => ['nullable', 'integer', 'min:1'],
        ]);

        $category = ComplaintCategory::query()->create($validated);
        AuditLogger::log($request, 'master.category.created', $category);

        return back()->with('success', 'Master category berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, ComplaintCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:complaint_categories,name,'.$category->id],
            'sla_label' => ['nullable', 'string', 'max:40'],
            'target_resolution_hours' => ['nullable', 'integer', 'min:1'],
        ]);

        $category->update($validated);
        AuditLogger::log($request, 'master.category.updated', $category);

        return back()->with('success', 'Master category berhasil diperbarui.');
    }

    public function deleteCategory(Request $request, ComplaintCategory $category): RedirectResponse
    {
        $category->delete();
        AuditLogger::log($request, 'master.category.deleted', null, ['category_id' => $category->id]);

        return back()->with('success', 'Master category berhasil dihapus.');
    }

    public function storeSeverity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60', 'unique:complaint_severities,name'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $severity = ComplaintSeverity::query()->create([
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        AuditLogger::log($request, 'master.severity.created', $severity);

        return back()->with('success', 'Master severity berhasil ditambahkan.');
    }

    public function updateSeverity(Request $request, ComplaintSeverity $severity): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60', 'unique:complaint_severities,name,'.$severity->id],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $severity->update([
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        AuditLogger::log($request, 'master.severity.updated', $severity);

        return back()->with('success', 'Master severity berhasil diperbarui.');
    }

    public function deleteSeverity(Request $request, ComplaintSeverity $severity): RedirectResponse
    {
        $severity->delete();
        AuditLogger::log($request, 'master.severity.deleted', null, ['severity_id' => $severity->id]);

        return back()->with('success', 'Master severity berhasil dihapus.');
    }

    public function storeCustomer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $customer = Customer::query()->create([
            ...$validated,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        AuditLogger::log($request, 'master.customer.created', $customer);

        return back()->with('success', 'Master customer berhasil ditambahkan.');
    }

    public function updateCustomer(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $customer->update([
            ...$validated,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        AuditLogger::log($request, 'master.customer.updated', $customer);

        return back()->with('success', 'Master customer berhasil diperbarui.');
    }

    public function deleteCustomer(Request $request, Customer $customer): RedirectResponse
    {
        $customer->delete();
        AuditLogger::log($request, 'master.customer.deleted', null, ['customer_id' => $customer->id]);

        return back()->with('success', 'Master customer berhasil dihapus.');
    }

    public function storeNotificationRecipient(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:notification_recipients,email'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $recipient = NotificationRecipient::query()->create([
            'name' => $validated['name'] ?? null,
            'email' => $validated['email'],
            'event_key' => NotificationRecipient::EVENT_COMPLAINT_CREATED,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        AuditLogger::log($request, 'master.notification_recipient.created', $recipient);

        return back()->with('success', 'Email penerima notifikasi berhasil ditambahkan.');
    }

    public function updateNotificationRecipient(Request $request, NotificationRecipient $recipient): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:notification_recipients,email,'.$recipient->id],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $recipient->update([
            'name' => $validated['name'] ?? null,
            'email' => $validated['email'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        AuditLogger::log($request, 'master.notification_recipient.updated', $recipient);

        return back()->with('success', 'Email penerima notifikasi berhasil diperbarui.');
    }

    public function deleteNotificationRecipient(Request $request, NotificationRecipient $recipient): RedirectResponse
    {
        $recipient->delete();
        AuditLogger::log($request, 'master.notification_recipient.deleted', null, ['recipient_id' => $recipient->id]);

        return back()->with('success', 'Email penerima notifikasi berhasil dihapus.');
    }
}
