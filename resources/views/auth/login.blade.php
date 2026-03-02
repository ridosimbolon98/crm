<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login CRM Complaint</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|space-grotesk:500,600,700" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="crm-body min-h-screen">
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -left-24 top-0 h-72 w-72 rounded-full bg-cyan-300/35 blur-3xl"></div>
        <div class="absolute -right-8 top-40 h-96 w-96 rounded-full bg-orange-300/30 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-lime-200/35 blur-3xl"></div>
    </div>

    <main class="mx-auto flex min-h-screen w-full max-w-7xl items-center justify-center px-4 sm:px-6">
        <section class="w-full max-w-md rounded-3xl border border-slate-200/85 bg-white/90 p-6 shadow-sm backdrop-blur-sm sm:p-8">
            <p class="font-display text-sm uppercase tracking-[0.2em] text-slate-500">Manufacturing CRM</p>
            <h1 class="mt-2 font-display text-2xl font-semibold text-slate-900">Login Complaint Center</h1>
            <p class="mt-1 text-sm text-slate-600">Masuk menggunakan akun operasional Anda.</p>

            @if ($errors->any())
                <div class="mt-4 rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="mt-5 grid gap-3">
                @csrf
                <input type="email" name="email" value="{{ old('email') }}" class="crm-input" placeholder="Email" required autofocus>
                <input type="password" name="password" class="crm-input" placeholder="Password" required>
                <button type="submit" class="crm-btn-primary">Masuk</button>
            </form>

            <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-600">
                <p class="font-semibold text-slate-700">Default Seeder Account</p>
                <p class="mt-1">admin@crm.local / password</p>
            </div>
        </section>
    </main>
</body>
</html>
