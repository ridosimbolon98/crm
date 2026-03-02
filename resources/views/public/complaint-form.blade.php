<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Complaint Customer</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|space-grotesk:500,600,700" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="crm-body min-h-screen">
    <main class="mx-auto w-full max-w-4xl px-4 py-8 sm:px-6">
        <section class="crm-panel">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Customer Complaint Form</p>
                    <h1 class="font-display text-2xl font-semibold text-slate-900">Sampaikan Keluhan Anda</h1>
                </div>
                <a href="{{ route('login') }}" class="crm-btn-secondary">Login Internal</a>
            </div>

            @if (session('success'))
                <div class="mb-4 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('public.complaints.store') }}" enctype="multipart/form-data" class="grid gap-3">
                @csrf

                <h2 class="font-display text-lg font-semibold text-slate-900">Data Diri</h2>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="full_name" value="{{ old('full_name') }}" class="crm-input" placeholder="Nama Lengkap *" required>
                    <input name="phone" value="{{ old('phone') }}" class="crm-input" placeholder="No. HP *" required>
                    <input name="email" type="email" value="{{ old('email') }}" class="crm-input" placeholder="Email">
                    <select name="gender" class="crm-input">
                        <option value="">Jenis Kelamin</option>
                        <option value="Male" @selected(old('gender') === 'Male')>Laki-laki</option>
                        <option value="Female" @selected(old('gender') === 'Female')>Perempuan</option>
                    </select>
                    <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="crm-input">
                </div>

                <h2 class="mt-2 font-display text-lg font-semibold text-slate-900">Domisili</h2>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="province" value="{{ old('province') }}" class="crm-input" placeholder="Provinsi">
                    <input name="city" value="{{ old('city') }}" class="crm-input" placeholder="Kota/Kabupaten">
                    <input name="district" value="{{ old('district') }}" class="crm-input" placeholder="Kecamatan">
                    <input name="subdistrict" value="{{ old('subdistrict') }}" class="crm-input" placeholder="Kelurahan">
                    <textarea name="address" class="crm-input sm:col-span-2" placeholder="Alamat lengkap">{{ old('address') }}</textarea>
                </div>

                <h2 class="mt-2 font-display text-lg font-semibold text-slate-900">Detail Keluhan</h2>
                <div class="grid gap-3 sm:grid-cols-2">
                    <select name="brand_id" class="crm-input">
                        <option value="">Pilih Brand</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}" @selected(old('brand_id') == $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                    <select name="complaint_category_id" class="crm-input">
                        <option value="">Pilih Kategori Keluhan</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('complaint_category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date') }}" class="crm-input">
                    <input name="purchase_location" value="{{ old('purchase_location') }}" class="crm-input" placeholder="Lokasi Pembelian">
                    <input name="production_code" value="{{ old('production_code') }}" class="crm-input sm:col-span-2" placeholder="Kode Produksi / Batch">
                    <textarea name="story" class="crm-input min-h-28 sm:col-span-2" placeholder="Ceritakan keluhan Anda secara detail *" required>{{ old('story') }}</textarea>
                </div>

                <div class="mt-1">
                    <input type="file" name="attachments[]" class="crm-input" multiple>
                    <p class="mt-1 text-xs text-slate-500">Lampiran opsional. Maks 5 file, 10MB/file (jpg/png/webp/mp4/pdf).</p>
                </div>

                <label class="mt-2 flex items-start gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="consent" value="1" class="mt-1" @checked(old('consent')) required>
                    <span>Saya menyetujui data yang saya kirim digunakan untuk proses tindak lanjut keluhan.</span>
                </label>

                <button class="crm-btn-primary mt-2" type="submit">Kirim Keluhan</button>
            </form>
        </section>
    </main>
</body>
</html>
