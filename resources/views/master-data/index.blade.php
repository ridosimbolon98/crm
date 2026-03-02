@extends('layouts.app')

@section('title', 'Master Data')

@php
    $tab = $activeTab ?? 'brand';
@endphp

@section('content')
    <section class="space-y-4">
        <article class="crm-panel">
            <h2 class="font-display text-xl font-semibold text-slate-900">Master Data (Admin Only)</h2>
            <p class="mt-1 text-sm text-slate-600">Kelola Brand, Category, Severity, dan Customer.</p>
        </article>

        <article class="crm-panel">
            <div class="flex flex-wrap gap-2">
                <button class="crm-tab {{ $tab === 'brand' ? 'is-active' : '' }}" data-tab="brand">Brand</button>
                <button class="crm-tab {{ $tab === 'category' ? 'is-active' : '' }}" data-tab="category">Category</button>
                <button class="crm-tab {{ $tab === 'severity' ? 'is-active' : '' }}" data-tab="severity">Severity</button>
                <button class="crm-tab {{ $tab === 'customer' ? 'is-active' : '' }}" data-tab="customer">Customer</button>
                <button class="crm-tab {{ $tab === 'notification' ? 'is-active' : '' }}" data-tab="notification">Notif Email</button>
            </div>

            <div class="mt-4">
                <section class="master-pane {{ $tab === 'brand' ? '' : 'hidden' }}" data-pane="brand">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="font-display text-lg font-semibold text-slate-900">Master Brand</h3>
                        <button class="crm-btn-primary" data-open-modal="modal-brand">Tambah Brand</button>
                    </div>
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-3 py-2">Nama</th>
                                    <th class="px-3 py-2">Kode</th>
                                    <th class="px-3 py-2">Deskripsi</th>
                                    <th class="px-3 py-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($brands as $brand)
                                    <tr>
                                        <td class="px-3 py-2">{{ $brand->name }}</td>
                                        <td class="px-3 py-2">{{ $brand->code }}</td>
                                        <td class="px-3 py-2">{{ $brand->description }}</td>
                                        <td class="px-3 py-2">
                                            <div class="flex flex-wrap gap-2">
                                                <button
                                                    type="button"
                                                    class="crm-btn-secondary"
                                                    data-open-brand-edit="modal-brand-edit"
                                                    data-brand-update-url="{{ route('master.brands.update', $brand) }}"
                                                    data-brand-name="{{ $brand->name }}"
                                                    data-brand-code="{{ $brand->code }}"
                                                    data-brand-description="{{ $brand->description ?? '' }}"
                                                >
                                                    Edit
                                                </button>
                                                <button class="crm-btn-secondary" type="submit" form="delete-brand-{{ $brand->id }}">Hapus</button>
                                            </div>
                                            <form id="delete-brand-{{ $brand->id }}" class="hidden" method="POST" action="{{ route('master.brands.delete', $brand) }}">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-3 py-4 text-center text-slate-500">Belum ada data brand.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="master-pane {{ $tab === 'category' ? '' : 'hidden' }}" data-pane="category">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="font-display text-lg font-semibold text-slate-900">Master Category</h3>
                        <button class="crm-btn-primary" data-open-modal="modal-category">Tambah Category</button>
                    </div>
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-3 py-2">Nama</th>
                                    <th class="px-3 py-2">SLA Label</th>
                                    <th class="px-3 py-2">Target Jam</th>
                                    <th class="px-3 py-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($categories as $category)
                                    <tr>
                                        <td class="px-3 py-2">{{ $category->name }}</td>
                                        <td class="px-3 py-2">{{ $category->sla_label }}</td>
                                        <td class="px-3 py-2">{{ $category->target_resolution_hours }}</td>
                                        <td class="px-3 py-2">
                                            <form method="POST" action="{{ route('master.categories.update', $category) }}" class="grid gap-2 md:grid-cols-[1fr_1fr_1fr_auto_auto]">
                                                @csrf
                                                @method('PUT')
                                                <input name="name" value="{{ $category->name }}" class="crm-input">
                                                <input name="sla_label" value="{{ $category->sla_label }}" class="crm-input">
                                                <input name="target_resolution_hours" type="number" value="{{ $category->target_resolution_hours }}" class="crm-input">
                                                <button class="crm-btn-secondary" type="submit">Update</button>
                                                <button class="crm-btn-secondary" type="submit" formaction="{{ route('master.categories.delete', $category) }}" formmethod="POST" onclick="event.preventDefault(); this.closest('form').querySelector('.delete-category').submit();">Hapus</button>
                                            </form>
                                            <form class="delete-category hidden" method="POST" action="{{ route('master.categories.delete', $category) }}">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-3 py-4 text-center text-slate-500">Belum ada data category.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="master-pane {{ $tab === 'severity' ? '' : 'hidden' }}" data-pane="severity">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="font-display text-lg font-semibold text-slate-900">Master Severity</h3>
                        <button class="crm-btn-primary" data-open-modal="modal-severity">Tambah Severity</button>
                    </div>
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-3 py-2">Nama</th>
                                    <th class="px-3 py-2">Urutan</th>
                                    <th class="px-3 py-2">Status</th>
                                    <th class="px-3 py-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($severities as $severity)
                                    <tr>
                                        <td class="px-3 py-2">{{ $severity->name }}</td>
                                        <td class="px-3 py-2">{{ $severity->sort_order }}</td>
                                        <td class="px-3 py-2">{{ $severity->is_active ? 'Active' : 'Inactive' }}</td>
                                        <td class="px-3 py-2">
                                            <form method="POST" action="{{ route('master.severities.update', $severity) }}" class="grid gap-2 md:grid-cols-[1fr_1fr_1fr_auto_auto]">
                                                @csrf
                                                @method('PUT')
                                                <input name="name" value="{{ $severity->name }}" class="crm-input">
                                                <input name="sort_order" type="number" value="{{ $severity->sort_order }}" class="crm-input">
                                                <select name="is_active" class="crm-input">
                                                    <option value="1" @selected($severity->is_active)>Active</option>
                                                    <option value="0" @selected(! $severity->is_active)>Inactive</option>
                                                </select>
                                                <button class="crm-btn-secondary" type="submit">Update</button>
                                                <button class="crm-btn-secondary" type="submit" formaction="{{ route('master.severities.delete', $severity) }}" formmethod="POST" onclick="event.preventDefault(); this.closest('form').querySelector('.delete-severity').submit();">Hapus</button>
                                            </form>
                                            <form class="delete-severity hidden" method="POST" action="{{ route('master.severities.delete', $severity) }}">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-3 py-4 text-center text-slate-500">Belum ada data severity.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="master-pane {{ $tab === 'customer' ? '' : 'hidden' }}" data-pane="customer">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="font-display text-lg font-semibold text-slate-900">Master Customer</h3>
                        <button class="crm-btn-primary" data-open-modal="modal-customer">Tambah Customer</button>
                    </div>
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-3 py-2">Nama</th>
                                    <th class="px-3 py-2">Telepon</th>
                                    <th class="px-3 py-2">Email</th>
                                    <th class="px-3 py-2">Kota</th>
                                    <th class="px-3 py-2">Status</th>
                                    <th class="px-3 py-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($customers as $customer)
                                    <tr>
                                        <td class="px-3 py-2">{{ $customer->name }}</td>
                                        <td class="px-3 py-2">{{ $customer->phone }}</td>
                                        <td class="px-3 py-2">{{ $customer->email }}</td>
                                        <td class="px-3 py-2">{{ $customer->city }}</td>
                                        <td class="px-3 py-2">{{ $customer->is_active ? 'Active' : 'Inactive' }}</td>
                                        <td class="px-3 py-2">
                                            <div class="flex flex-wrap gap-2">
                                                <button
                                                    type="button"
                                                    class="crm-btn-secondary"
                                                    data-open-customer-edit="modal-customer-edit"
                                                    data-customer-update-url="{{ route('master.customers.update', $customer) }}"
                                                    data-customer-name="{{ $customer->name }}"
                                                    data-customer-phone="{{ $customer->phone ?? '' }}"
                                                    data-customer-email="{{ $customer->email ?? '' }}"
                                                    data-customer-city="{{ $customer->city ?? '' }}"
                                                    data-customer-address="{{ $customer->address ?? '' }}"
                                                    data-customer-active="{{ $customer->is_active ? '1' : '0' }}"
                                                >
                                                    Edit
                                                </button>
                                                <button class="crm-btn-secondary" type="submit" form="delete-customer-{{ $customer->id }}">Hapus</button>
                                            </div>
                                            <form id="delete-customer-{{ $customer->id }}" class="hidden" method="POST" action="{{ route('master.customers.delete', $customer) }}">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-3 py-4 text-center text-slate-500">Belum ada data customer.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $customers->appends(['tab' => 'customer'])->links() }}
                    </div>
                </section>

                <section class="master-pane {{ $tab === 'notification' ? '' : 'hidden' }}" data-pane="notification">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="font-display text-lg font-semibold text-slate-900">Penerima Notifikasi Complaint Masuk</h3>
                        <button class="crm-btn-primary" data-open-modal="modal-notification-recipient">Tambah Email Penerima</button>
                    </div>
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-3 py-2">Nama</th>
                                    <th class="px-3 py-2">Email</th>
                                    <th class="px-3 py-2">Event</th>
                                    <th class="px-3 py-2">Status</th>
                                    <th class="px-3 py-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($notificationRecipients as $recipient)
                                    <tr>
                                        <td class="px-3 py-2">{{ $recipient->name ?: '-' }}</td>
                                        <td class="px-3 py-2">{{ $recipient->email }}</td>
                                        <td class="px-3 py-2">{{ $recipient->event_key }}</td>
                                        <td class="px-3 py-2">{{ $recipient->is_active ? 'Active' : 'Inactive' }}</td>
                                        <td class="px-3 py-2">
                                            <div class="flex flex-wrap gap-2">
                                                <button
                                                    type="button"
                                                    class="crm-btn-secondary"
                                                    data-open-recipient-edit="modal-notification-recipient-edit"
                                                    data-recipient-update-url="{{ route('master.notification_recipients.update', $recipient) }}"
                                                    data-recipient-name="{{ $recipient->name ?? '' }}"
                                                    data-recipient-email="{{ $recipient->email }}"
                                                    data-recipient-active="{{ $recipient->is_active ? '1' : '0' }}"
                                                >
                                                    Edit
                                                </button>
                                                <button class="crm-btn-secondary" type="submit" form="delete-recipient-{{ $recipient->id }}">Hapus</button>
                                            </div>
                                            <form id="delete-recipient-{{ $recipient->id }}" class="hidden" method="POST" action="{{ route('master.notification_recipients.delete', $recipient) }}">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-3 py-4 text-center text-slate-500">Belum ada data email penerima notifikasi.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </article>
    </section>

    <div class="crm-modal hidden" id="modal-brand">
        <div class="crm-modal-card">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-lg font-semibold text-slate-900">Tambah Brand</h3>
                <button class="crm-btn-secondary" data-close-modal="modal-brand">Tutup</button>
            </div>
            <form method="POST" action="{{ route('master.brands.store') }}" class="mt-3 grid gap-3">
                @csrf
                <input name="name" class="crm-input" placeholder="Nama Brand" required>
                <input name="code" class="crm-input" placeholder="Kode" required>
                <input name="description" class="crm-input" placeholder="Deskripsi">
                <button class="crm-btn-primary" type="submit">Simpan</button>
            </form>
        </div>
    </div>

    <div class="crm-modal hidden" id="modal-brand-edit">
        <div class="crm-modal-card">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-lg font-semibold text-slate-900">Edit Brand</h3>
                <button class="crm-btn-secondary" data-close-modal="modal-brand-edit">Tutup</button>
            </div>
            <form method="POST" action="#" id="brand-edit-form" class="mt-3 grid gap-3">
                @csrf
                @method('PUT')
                <input name="name" id="brand-edit-name" class="crm-input" placeholder="Nama Brand" required>
                <input name="code" id="brand-edit-code" class="crm-input" placeholder="Kode" required>
                <input name="description" id="brand-edit-description" class="crm-input" placeholder="Deskripsi">
                <button class="crm-btn-primary" type="submit">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <div class="crm-modal hidden" id="modal-category">
        <div class="crm-modal-card">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-lg font-semibold text-slate-900">Tambah Category</h3>
                <button class="crm-btn-secondary" data-close-modal="modal-category">Tutup</button>
            </div>
            <form method="POST" action="{{ route('master.categories.store') }}" class="mt-3 grid gap-3">
                @csrf
                <input name="name" class="crm-input" placeholder="Nama Category" required>
                <input name="sla_label" class="crm-input" placeholder="SLA Label">
                <input name="target_resolution_hours" type="number" class="crm-input" placeholder="Target Jam">
                <button class="crm-btn-primary" type="submit">Simpan</button>
            </form>
        </div>
    </div>

    <div class="crm-modal hidden" id="modal-severity">
        <div class="crm-modal-card">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-lg font-semibold text-slate-900">Tambah Severity</h3>
                <button class="crm-btn-secondary" data-close-modal="modal-severity">Tutup</button>
            </div>
            <form method="POST" action="{{ route('master.severities.store') }}" class="mt-3 grid gap-3">
                @csrf
                <input name="name" class="crm-input" placeholder="Nama Severity" required>
                <input name="sort_order" type="number" class="crm-input" placeholder="Urutan">
                <select name="is_active" class="crm-input">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <button class="crm-btn-primary" type="submit">Simpan</button>
            </form>
        </div>
    </div>

    <div class="crm-modal hidden" id="modal-customer">
        <div class="crm-modal-card">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-lg font-semibold text-slate-900">Tambah Customer</h3>
                <button class="crm-btn-secondary" data-close-modal="modal-customer">Tutup</button>
            </div>
            <form method="POST" action="{{ route('master.customers.store') }}" class="mt-3 grid gap-3">
                @csrf
                <input name="name" class="crm-input" placeholder="Nama Customer" required>
                <input name="phone" class="crm-input" placeholder="Telepon">
                <input name="email" type="email" class="crm-input" placeholder="Email">
                <input name="city" class="crm-input" placeholder="Kota">
                <textarea name="address" class="crm-input" placeholder="Alamat"></textarea>
                <select name="is_active" class="crm-input">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <button class="crm-btn-primary" type="submit">Simpan</button>
            </form>
        </div>
    </div>

    <div class="crm-modal hidden" id="modal-customer-edit">
        <div class="crm-modal-card">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-lg font-semibold text-slate-900">Edit Customer</h3>
                <button class="crm-btn-secondary" data-close-modal="modal-customer-edit">Tutup</button>
            </div>
            <form method="POST" action="#" id="customer-edit-form" class="mt-3 grid gap-3">
                @csrf
                @method('PUT')
                <input name="name" id="customer-edit-name" class="crm-input" placeholder="Nama Customer" required>
                <input name="phone" id="customer-edit-phone" class="crm-input" placeholder="Telepon">
                <input name="email" id="customer-edit-email" type="email" class="crm-input" placeholder="Email">
                <input name="city" id="customer-edit-city" class="crm-input" placeholder="Kota">
                <textarea name="address" id="customer-edit-address" class="crm-input" placeholder="Alamat"></textarea>
                <select name="is_active" id="customer-edit-active" class="crm-input">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <button class="crm-btn-primary" type="submit">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <div class="crm-modal hidden" id="modal-notification-recipient">
        <div class="crm-modal-card">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-lg font-semibold text-slate-900">Tambah Email Penerima</h3>
                <button class="crm-btn-secondary" data-close-modal="modal-notification-recipient">Tutup</button>
            </div>
            <form method="POST" action="{{ route('master.notification_recipients.store') }}" class="mt-3 grid gap-3">
                @csrf
                <input name="name" class="crm-input" placeholder="Nama (opsional)">
                <input name="email" type="email" class="crm-input" placeholder="Email Penerima" required>
                <select name="is_active" class="crm-input">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <button class="crm-btn-primary" type="submit">Simpan</button>
            </form>
        </div>
    </div>

    <div class="crm-modal hidden" id="modal-notification-recipient-edit">
        <div class="crm-modal-card">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-lg font-semibold text-slate-900">Edit Email Penerima</h3>
                <button class="crm-btn-secondary" data-close-modal="modal-notification-recipient-edit">Tutup</button>
            </div>
            <form method="POST" action="#" id="notification-recipient-edit-form" class="mt-3 grid gap-3">
                @csrf
                @method('PUT')
                <input name="name" id="notification-recipient-edit-name" class="crm-input" placeholder="Nama (opsional)">
                <input name="email" id="notification-recipient-edit-email" type="email" class="crm-input" placeholder="Email Penerima" required>
                <select name="is_active" id="notification-recipient-edit-active" class="crm-input">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <button class="crm-btn-primary" type="submit">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const tabs = document.querySelectorAll('.crm-tab');
            const panes = document.querySelectorAll('.master-pane');

            const activateTab = (tabName) => {
                tabs.forEach((btn) => btn.classList.toggle('is-active', btn.dataset.tab === tabName));
                panes.forEach((pane) => pane.classList.toggle('hidden', pane.dataset.pane !== tabName));
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tabName);
                window.history.replaceState({}, '', url);
            };

            tabs.forEach((btn) => {
                btn.addEventListener('click', () => activateTab(btn.dataset.tab));
            });

            document.querySelectorAll('[data-open-modal]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const modal = document.getElementById(btn.dataset.openModal);
                    if (modal) modal.classList.remove('hidden');
                });
            });

            const brandEditModal = document.getElementById('modal-brand-edit');
            const brandEditForm = document.getElementById('brand-edit-form');
            const brandEditName = document.getElementById('brand-edit-name');
            const brandEditCode = document.getElementById('brand-edit-code');
            const brandEditDescription = document.getElementById('brand-edit-description');

            document.querySelectorAll('[data-open-brand-edit]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    if (!brandEditModal || !brandEditForm) return;
                    brandEditForm.action = btn.dataset.brandUpdateUrl || '#';
                    if (brandEditName) brandEditName.value = btn.dataset.brandName || '';
                    if (brandEditCode) brandEditCode.value = btn.dataset.brandCode || '';
                    if (brandEditDescription) brandEditDescription.value = btn.dataset.brandDescription || '';
                    brandEditModal.classList.remove('hidden');
                });
            });

            const customerEditModal = document.getElementById('modal-customer-edit');
            const customerEditForm = document.getElementById('customer-edit-form');
            const customerEditName = document.getElementById('customer-edit-name');
            const customerEditPhone = document.getElementById('customer-edit-phone');
            const customerEditEmail = document.getElementById('customer-edit-email');
            const customerEditCity = document.getElementById('customer-edit-city');
            const customerEditAddress = document.getElementById('customer-edit-address');
            const customerEditActive = document.getElementById('customer-edit-active');

            document.querySelectorAll('[data-open-customer-edit]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    if (!customerEditModal || !customerEditForm) return;
                    customerEditForm.action = btn.dataset.customerUpdateUrl || '#';
                    if (customerEditName) customerEditName.value = btn.dataset.customerName || '';
                    if (customerEditPhone) customerEditPhone.value = btn.dataset.customerPhone || '';
                    if (customerEditEmail) customerEditEmail.value = btn.dataset.customerEmail || '';
                    if (customerEditCity) customerEditCity.value = btn.dataset.customerCity || '';
                    if (customerEditAddress) customerEditAddress.value = btn.dataset.customerAddress || '';
                    if (customerEditActive) customerEditActive.value = btn.dataset.customerActive || '1';
                    customerEditModal.classList.remove('hidden');
                });
            });

            const recipientEditModal = document.getElementById('modal-notification-recipient-edit');
            const recipientEditForm = document.getElementById('notification-recipient-edit-form');
            const recipientEditName = document.getElementById('notification-recipient-edit-name');
            const recipientEditEmail = document.getElementById('notification-recipient-edit-email');
            const recipientEditActive = document.getElementById('notification-recipient-edit-active');

            document.querySelectorAll('[data-open-recipient-edit]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    if (!recipientEditModal || !recipientEditForm) return;
                    recipientEditForm.action = btn.dataset.recipientUpdateUrl || '#';
                    if (recipientEditName) recipientEditName.value = btn.dataset.recipientName || '';
                    if (recipientEditEmail) recipientEditEmail.value = btn.dataset.recipientEmail || '';
                    if (recipientEditActive) recipientEditActive.value = btn.dataset.recipientActive || '1';
                    recipientEditModal.classList.remove('hidden');
                });
            });

            document.querySelectorAll('[data-close-modal]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const modal = document.getElementById(btn.dataset.closeModal);
                    if (modal) modal.classList.add('hidden');
                });
            });

            document.querySelectorAll('.crm-modal').forEach((modal) => {
                modal.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        modal.classList.add('hidden');
                    }
                });
            });
        })();
    </script>
@endsection
