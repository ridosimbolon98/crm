@extends('layouts.app')

@section('title', 'Input Ticket Complaint')

@section('content')
    <section class="mx-auto max-w-4xl">
        <article class="crm-panel">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="font-display text-lg font-semibold text-slate-900">Input Ticket Complaint Baru</h2>
                    <p class="text-sm text-slate-500">Isi data complaint customer secara lengkap untuk proses investigasi.</p>
                </div>
                <a href="{{ route('complaints.index') }}" class="crm-btn-secondary">Kembali ke List</a>
            </div>

            <form method="POST" action="{{ route('complaints.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-3">
                @csrf
                @php
                    $selectedCustomer = $customers->firstWhere('id', (int) old('customer_id'));
                    $selectedCustomerLabel = $selectedCustomer ? ($selectedCustomer->name.($selectedCustomer->phone ? ' - '.$selectedCustomer->phone : '')) : '';
                @endphp
                <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id') }}">
                <input
                    list="customer-list"
                    id="customer_search"
                    class="crm-input"
                    placeholder="Cari Master Customer (ketik nama/telepon)"
                    value="{{ old('customer_search', $selectedCustomerLabel) }}"
                >
                <datalist id="customer-list">
                    @foreach ($customers as $customer)
                        <option
                            value="{{ $customer->name }}{{ $customer->phone ? ' - '.$customer->phone : '' }}"
                            data-id="{{ $customer->id }}"
                            data-name="{{ $customer->name }}"
                            data-phone="{{ $customer->phone ?? '' }}"
                            data-email="{{ $customer->email ?? '' }}"
                        ></option>
                    @endforeach
                </datalist>
                <input name="customer_name" id="customer_name" value="{{ old('customer_name') }}" class="crm-input" placeholder="Nama customer *" required>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="customer_phone" id="customer_phone" value="{{ old('customer_phone') }}" class="crm-input" placeholder="No. telepon">
                    <input name="customer_email" id="customer_email" value="{{ old('customer_email') }}" type="email" class="crm-input" placeholder="Email">
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <select name="brand_id" class="crm-input">
                        <option value="">Pilih Brand</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}" @selected(old('brand_id') == $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                    <select name="complaint_category_id" class="crm-input">
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('complaint_category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <select name="complaint_channel" class="crm-input" required>
                        @foreach ($channelOptions as $channel)
                            <option value="{{ $channel }}" @selected(old('complaint_channel', 'Phone') === $channel)>{{ $channel }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="complaint_date" value="{{ old('complaint_date', now()->toDateString()) }}" class="crm-input" required>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <select name="severity" class="crm-input" required>
                        @foreach ($severityOptions as $severity)
                            <option value="{{ $severity }}" @selected(old('severity', 'Medium') === $severity)>{{ $severity }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="crm-input" required>
                        @foreach ($statusOptions as $status)
                            <option value="{{ $status }}" @selected(old('status', 'Open') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <input name="production_code" value="{{ old('production_code') }}" class="crm-input" placeholder="Kode produksi / batch">
                <input name="assigned_to" value="{{ old('assigned_to') }}" class="crm-input" placeholder="PIC (QA / Sales / Area)">
                <input type="date" name="target_resolution_date" value="{{ old('target_resolution_date') }}" class="crm-input">
                <textarea name="description" class="crm-input min-h-24" placeholder="Deskripsi keluhan *" required>{{ old('description') }}</textarea>
                <input type="file" name="attachments[]" class="crm-input" multiple>
                <p class="text-xs text-slate-500">Maks 5 file, tiap file maks 10MB (jpg/png/webp/mp4/pdf).</p>
                <button class="crm-btn-primary" type="submit">Simpan Ticket</button>
            </form>
        </article>
    </section>

    <script>
        (function () {
            const searchInput = document.getElementById('customer_search');
            const customerIdInput = document.getElementById('customer_id');
            const customerNameInput = document.getElementById('customer_name');
            const customerPhoneInput = document.getElementById('customer_phone');
            const customerEmailInput = document.getElementById('customer_email');
            const datalist = document.getElementById('customer-list');

            if (!searchInput || !customerIdInput || !customerNameInput || !customerPhoneInput || !customerEmailInput || !datalist) {
                return;
            }

            const syncCustomerId = () => {
                const value = searchInput.value.trim();
                const options = Array.from(datalist.options);
                const matched = options.find((opt) => opt.value === value);
                if (matched) {
                    customerIdInput.value = matched.dataset.id || '';
                    customerNameInput.value = matched.dataset.name || '';
                    customerPhoneInput.value = matched.dataset.phone || '';
                    customerEmailInput.value = matched.dataset.email || '';
                    return;
                }

                customerIdInput.value = '';
            };

            searchInput.addEventListener('change', syncCustomerId);
            searchInput.addEventListener('blur', syncCustomerId);
        })();
    </script>
@endsection
