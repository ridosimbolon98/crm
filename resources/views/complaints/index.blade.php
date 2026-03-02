@extends('layouts.app')

@section('title', 'Daftar Complaint')

@php
    $statusTone = static function (string $status): string {
        return match ($status) {
            'Open' => 'bg-amber-100 text-amber-800',
            'Investigating' => 'bg-cyan-100 text-cyan-800',
            'Action Plan' => 'bg-indigo-100 text-indigo-800',
            'Resolved' => 'bg-emerald-100 text-emerald-800',
            'Closed' => 'bg-slate-200 text-slate-700',
            default => 'bg-slate-100 text-slate-700',
        };
    };

    $severityTone = static function (string $severity): string {
        return match ($severity) {
            'Low' => 'bg-emerald-100 text-emerald-800',
            'Medium' => 'bg-cyan-100 text-cyan-800',
            'High' => 'bg-orange-100 text-orange-800',
            'Critical' => 'bg-rose-100 text-rose-800',
            default => 'bg-slate-100 text-slate-700',
        };
    };
@endphp

@section('content')
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="crm-card">
            <p class="crm-card-label">Total Complaint</p>
            <p class="crm-card-value">{{ number_format($summary['total']) }}</p>
        </article>
        <article class="crm-card">
            <p class="crm-card-label">Masih Aktif</p>
            <p class="crm-card-value">{{ number_format($summary['open']) }}</p>
        </article>
        <article class="crm-card">
            <p class="crm-card-label">Closed Hari Ini</p>
            <p class="crm-card-value">{{ number_format($summary['resolved_today']) }}</p>
        </article>
        <article class="crm-card">
            <p class="crm-card-label">Lewat SLA</p>
            <p class="crm-card-value text-rose-600">{{ number_format($summary['overdue']) }}</p>
        </article>
    </section>

    <section class="mt-4 grid gap-4">
        <article class="crm-panel">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-display text-lg font-semibold text-slate-900">Filter & Monitoring</h2>
                <div class="flex flex-wrap gap-2">
                    @if ($canExport)
                        <a href="{{ route('complaints.export.excel', request()->query()) }}" class="crm-btn-secondary">Export Excel</a>
                        <a href="{{ route('complaints.export.pdf', request()->query()) }}" class="crm-btn-secondary" target="_blank">Export PDF</a>
                    @endif
                    @if ($canCreate)
                        <a href="{{ route('complaints.create') }}" class="crm-btn-primary">Input Ticket Baru</a>
                    @endif
                </div>
            </div>

            <form method="GET" action="{{ route('complaints.index') }}" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari tiket / customer / kode produksi" class="crm-input sm:col-span-2 lg:col-span-3">
                <select name="status" class="crm-input">
                    <option value="">Semua Status</option>
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <select name="severity" class="crm-input">
                    <option value="">Semua Severity</option>
                    @foreach ($severityOptions as $severity)
                        <option value="{{ $severity }}" @selected(($filters['severity'] ?? '') === $severity)>{{ $severity }}</option>
                    @endforeach
                </select>
                <select name="brand" class="crm-input">
                    <option value="">Semua Brand</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}" @selected(($filters['brand'] ?? '') == $brand->id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="crm-input">
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="crm-input">
                <div class="flex gap-2">
                    <button class="crm-btn-primary w-full" type="submit">Terapkan</button>
                    <a href="{{ route('complaints.index') }}" class="crm-btn-secondary w-full text-center">Reset</a>
                </div>
            </form>
        </article>

        <article class="crm-panel">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-lg font-semibold text-slate-900">Daftar Ticket</h2>
                <span class="text-sm text-slate-500">{{ $complaints->total() }} data</span>
            </div>

            <div class="mt-4 hidden overflow-x-auto lg:block">
                <table class="w-full text-left text-sm">
                        <thead class="text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="pb-2">Ticket</th>
                                <th class="pb-2">Customer</th>
                                <th class="pb-2">Brand</th>
                                <th class="pb-2">Kode Produksi</th>
                                <th class="pb-2">Severity</th>
                                <th class="pb-2">Status</th>
                                <th class="pb-2">CAPA</th>
                                <th class="pb-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($complaints as $complaint)
                                <tr class="align-top border-b border-slate-200">
                                    <td class="py-3 pr-2 font-semibold text-slate-800">{{ $complaint->ticket_number }}</td>
                                    <td class="py-3 pr-2">
                                        <p class="font-medium text-slate-700">{{ $complaint->customer_name }}</p>
                                        <p class="text-xs text-slate-500">{{ $complaint->complaint_date?->format('d M Y') }}</p>
                                    </td>
                                    <td class="py-3 pr-2 text-slate-700">{{ $complaint->brand?->name ?? '-' }}</td>
                                    <td class="py-3 pr-2 text-slate-700">{{ $complaint->production_code ?: '-' }}</td>
                                    <td class="py-3 pr-2">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $severityTone($complaint->severity) }}">{{ $complaint->severity }}</span>
                                    </td>
                                    <td class="py-3 pr-2">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusTone($complaint->status) }}">{{ $complaint->status }}</span>
                                    </td>
                                    <td class="py-3 pr-2 text-xs text-slate-600">{{ $complaint->capa_status }}</td>
                                    <td class="py-3 text-right">
                                        <a href="{{ route('complaints.show', $complaint) }}" class="text-sm font-semibold text-cyan-700 hover:text-cyan-900">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-500">Belum ada data complaint.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            <div class="mt-4 grid gap-3 lg:hidden">
                @forelse ($complaints as $complaint)
                    <div class="rounded-2xl border border-slate-200 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $complaint->ticket_number }}</p>
                                    <p class="text-sm text-slate-600">{{ $complaint->customer_name }}</p>
                                    <p class="text-xs text-slate-500">Kode Produksi: {{ $complaint->production_code ?: '-' }}</p>
                                </div>
                                <a href="{{ route('complaints.show', $complaint) }}" class="text-sm font-semibold text-cyan-700">Detail</a>
                            </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $severityTone($complaint->severity) }}">{{ $complaint->severity }}</span>
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusTone($complaint->status) }}">{{ $complaint->status }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-sm text-slate-500">Belum ada data complaint.</p>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $complaints->links() }}
            </div>
        </article>
    </section>
@endsection
