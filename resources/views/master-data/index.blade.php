@extends('layouts.app')

@section('title', 'Master Data')

@section('content')
    <section class="space-y-4">
        <article class="crm-panel">
            <h2 class="font-display text-xl font-semibold text-slate-900">Master Data (Admin Only)</h2>
            <p class="mt-1 text-sm text-slate-600">Kelola Brand, Category, Severity, dan Customer.</p>
        </article>

        <article class="crm-panel">
            <h3 class="font-display text-lg font-semibold text-slate-900">Master Brand</h3>
            <form method="POST" action="{{ route('master.brands.store') }}" class="mt-3 grid gap-3 sm:grid-cols-4">
                @csrf
                <input name="name" class="crm-input" placeholder="Nama Brand" required>
                <input name="code" class="crm-input" placeholder="Kode" required>
                <input name="description" class="crm-input sm:col-span-2" placeholder="Deskripsi">
                <button class="crm-btn-primary sm:col-span-4" type="submit">Tambah Brand</button>
            </form>
            <div class="mt-3 space-y-2">
                @foreach ($brands as $brand)
                    <form method="POST" action="{{ route('master.brands.update', $brand) }}" class="grid gap-2 rounded-xl border border-slate-200 p-3 sm:grid-cols-12">
                        @csrf
                        @method('PUT')
                        <input name="name" value="{{ $brand->name }}" class="crm-input sm:col-span-4" required>
                        <input name="code" value="{{ $brand->code }}" class="crm-input sm:col-span-2" required>
                        <input name="description" value="{{ $brand->description }}" class="crm-input sm:col-span-4">
                        <button class="crm-btn-secondary sm:col-span-1" type="submit">Update</button>
                        <button class="crm-btn-secondary sm:col-span-1" type="submit" form="delete-brand-{{ $brand->id }}">Hapus</button>
                    </form>
                    <form id="delete-brand-{{ $brand->id }}" method="POST" action="{{ route('master.brands.delete', $brand) }}">
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach
            </div>
        </article>

        <article class="crm-panel">
            <h3 class="font-display text-lg font-semibold text-slate-900">Master Category</h3>
            <form method="POST" action="{{ route('master.categories.store') }}" class="mt-3 grid gap-3 sm:grid-cols-4">
                @csrf
                <input name="name" class="crm-input sm:col-span-2" placeholder="Nama Category" required>
                <input name="sla_label" class="crm-input" placeholder="SLA Label (contoh: 24 jam)">
                <input name="target_resolution_hours" type="number" class="crm-input" placeholder="Target jam">
                <button class="crm-btn-primary sm:col-span-4" type="submit">Tambah Category</button>
            </form>
            <div class="mt-3 space-y-2">
                @foreach ($categories as $category)
                    <form method="POST" action="{{ route('master.categories.update', $category) }}" class="grid gap-2 rounded-xl border border-slate-200 p-3 sm:grid-cols-12">
                        @csrf
                        @method('PUT')
                        <input name="name" value="{{ $category->name }}" class="crm-input sm:col-span-5" required>
                        <input name="sla_label" value="{{ $category->sla_label }}" class="crm-input sm:col-span-3">
                        <input name="target_resolution_hours" type="number" value="{{ $category->target_resolution_hours }}" class="crm-input sm:col-span-2">
                        <button class="crm-btn-secondary sm:col-span-1" type="submit">Update</button>
                        <button class="crm-btn-secondary sm:col-span-1" type="submit" form="delete-category-{{ $category->id }}">Hapus</button>
                    </form>
                    <form id="delete-category-{{ $category->id }}" method="POST" action="{{ route('master.categories.delete', $category) }}">
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach
            </div>
        </article>

        <article class="crm-panel">
            <h3 class="font-display text-lg font-semibold text-slate-900">Master Severity</h3>
            <form method="POST" action="{{ route('master.severities.store') }}" class="mt-3 grid gap-3 sm:grid-cols-4">
                @csrf
                <input name="name" class="crm-input sm:col-span-2" placeholder="Nama Severity" required>
                <input name="sort_order" type="number" class="crm-input" placeholder="Urutan">
                <select name="is_active" class="crm-input">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <button class="crm-btn-primary sm:col-span-4" type="submit">Tambah Severity</button>
            </form>
            <div class="mt-3 space-y-2">
                @foreach ($severities as $severity)
                    <form method="POST" action="{{ route('master.severities.update', $severity) }}" class="grid gap-2 rounded-xl border border-slate-200 p-3 sm:grid-cols-12">
                        @csrf
                        @method('PUT')
                        <input name="name" value="{{ $severity->name }}" class="crm-input sm:col-span-5" required>
                        <input name="sort_order" type="number" value="{{ $severity->sort_order }}" class="crm-input sm:col-span-2">
                        <select name="is_active" class="crm-input sm:col-span-3">
                            <option value="1" @selected($severity->is_active)>Active</option>
                            <option value="0" @selected(! $severity->is_active)>Inactive</option>
                        </select>
                        <button class="crm-btn-secondary sm:col-span-1" type="submit">Update</button>
                        <button class="crm-btn-secondary sm:col-span-1" type="submit" form="delete-severity-{{ $severity->id }}">Hapus</button>
                    </form>
                    <form id="delete-severity-{{ $severity->id }}" method="POST" action="{{ route('master.severities.delete', $severity) }}">
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach
            </div>
        </article>

        <article class="crm-panel">
            <h3 class="font-display text-lg font-semibold text-slate-900">Master Customer</h3>
            <form method="POST" action="{{ route('master.customers.store') }}" class="mt-3 grid gap-3 sm:grid-cols-2">
                @csrf
                <input name="name" class="crm-input" placeholder="Nama Customer" required>
                <input name="phone" class="crm-input" placeholder="Telepon">
                <input name="email" type="email" class="crm-input" placeholder="Email">
                <input name="city" class="crm-input" placeholder="Kota">
                <textarea name="address" class="crm-input sm:col-span-2" placeholder="Alamat"></textarea>
                <select name="is_active" class="crm-input sm:col-span-2">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <button class="crm-btn-primary sm:col-span-2" type="submit">Tambah Customer</button>
            </form>
            <div class="mt-3 space-y-2">
                @foreach ($customers as $customer)
                    <form method="POST" action="{{ route('master.customers.update', $customer) }}" class="grid gap-2 rounded-xl border border-slate-200 p-3 sm:grid-cols-12">
                        @csrf
                        @method('PUT')
                        <input name="name" value="{{ $customer->name }}" class="crm-input sm:col-span-3" required>
                        <input name="phone" value="{{ $customer->phone }}" class="crm-input sm:col-span-2">
                        <input name="email" value="{{ $customer->email }}" class="crm-input sm:col-span-3">
                        <input name="city" value="{{ $customer->city }}" class="crm-input sm:col-span-2">
                        <select name="is_active" class="crm-input sm:col-span-1">
                            <option value="1" @selected($customer->is_active)>Y</option>
                            <option value="0" @selected(! $customer->is_active)>N</option>
                        </select>
                        <button class="crm-btn-secondary sm:col-span-1" type="submit">Upd</button>
                        <textarea name="address" class="crm-input sm:col-span-10" placeholder="Alamat">{{ $customer->address }}</textarea>
                        <button class="crm-btn-secondary sm:col-span-2" type="submit" form="delete-customer-{{ $customer->id }}">Hapus</button>
                    </form>
                    <form id="delete-customer-{{ $customer->id }}" method="POST" action="{{ route('master.customers.delete', $customer) }}">
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach
            </div>
            <div class="mt-4">
                {{ $customers->links() }}
            </div>
        </article>
    </section>
@endsection
