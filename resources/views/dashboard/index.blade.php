@extends('layouts.app')

@section('title', 'Dashboard KPI')

@section('content')
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <article class="crm-card">
            <p class="crm-card-label">Total Complaint</p>
            <p class="crm-card-value">{{ number_format($cards['total']) }}</p>
        </article>
        <article class="crm-card">
            <p class="crm-card-label">Open Ticket</p>
            <p class="crm-card-value">{{ number_format($cards['open']) }}</p>
        </article>
        <article class="crm-card">
            <p class="crm-card-label">Closed 30 Hari</p>
            <p class="crm-card-value">{{ number_format($cards['closed_30d']) }}</p>
        </article>
        <article class="crm-card">
            <p class="crm-card-label">Avg Resolution</p>
            <p class="crm-card-value">{{ $cards['avg_resolution_hours'] !== null ? $cards['avg_resolution_hours'].' jam' : '-' }}</p>
        </article>
        <article class="crm-card">
            <p class="crm-card-label">SLA Compliance</p>
            <p class="crm-card-value">{{ number_format($cards['sla_rate'], 2) }}%</p>
        </article>
    </section>

    <section class="mt-4 grid gap-4 xl:grid-cols-2">
        <article class="crm-panel">
            <div class="flex items-center justify-between gap-4">
                <h2 class="font-display text-lg font-semibold text-slate-900">Distribusi Status</h2>
                <span class="text-xs text-slate-500">All Time</span>
            </div>
            <div class="mt-4 space-y-3">
                @php
                    $statusTotal = max(1, $statusStats->sum());
                @endphp
                @forelse ($statusStats as $status => $total)
                    @php
                        $percent = round(($total / $statusTotal) * 100, 2);
                    @endphp
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700">{{ $status }}</span>
                            <span class="text-slate-500">{{ $total }} ({{ $percent }}%)</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-cyan-500" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada data.</p>
                @endforelse
            </div>
        </article>

        <article class="crm-panel">
            <div class="flex items-center justify-between gap-4">
                <h2 class="font-display text-lg font-semibold text-slate-900">Top Brand (30 Hari)</h2>
                <span class="text-xs text-slate-500">Sejak {{ \Illuminate\Support\Carbon::parse($dateFrom)->format('d M Y') }}</span>
            </div>
            <div class="mt-4 space-y-3">
                @php
                    $maxBrandTotal = max(1, (int) $brandStats->max('total'));
                @endphp
                @forelse ($brandStats as $item)
                    @php
                        $percent = round(($item->total / $maxBrandTotal) * 100, 2);
                    @endphp
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700">{{ $item->name }}</span>
                            <span class="text-slate-500">{{ $item->total }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-orange-500" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada data brand.</p>
                @endforelse
            </div>
        </article>
    </section>
@endsection
