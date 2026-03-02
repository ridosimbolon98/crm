<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CRM Complaint')</title>
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

    <header class="border-b border-slate-200/80 bg-white/85 backdrop-blur-xl">
        <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <div>
                <p class="font-display text-sm uppercase tracking-[0.24em] text-slate-500">Manufacturing CRM</p>
                <h1 class="mt-1 font-display text-xl font-semibold text-slate-900 sm:text-2xl">Customer Complaint Center</h1>
            </div>
            <div class="flex items-center gap-2">
                <nav class="hidden items-center gap-2 sm:flex">
                    <a href="{{ route('dashboard.index') }}" class="crm-btn-secondary">Dashboard</a>
                    <a href="{{ route('complaints.index') }}" class="crm-btn-secondary">Complaint</a>
                    <a href="{{ route('guide.index') }}" class="crm-btn-secondary">Panduan</a>
                    @if (auth()->user()?->hasRole('admin'))
                        <a href="{{ route('master.index') }}" class="crm-btn-secondary">Master Data</a>
                    @endif
                </nav>
                <div class="hidden items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-xs font-medium text-slate-600 sm:flex">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    {{ auth()->user()?->name }} ({{ strtoupper((string) auth()->user()?->role) }})
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="crm-btn-primary">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-4 rounded-2xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-2xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ $errors->first() }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
