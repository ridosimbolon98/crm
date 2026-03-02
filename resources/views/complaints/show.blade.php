@extends('layouts.app')

@section('title', 'Detail Complaint')

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
@endphp

@section('content')
    <section class="grid gap-4 xl:grid-cols-[1.65fr_1fr]">
        <div class="space-y-4">
            <article class="crm-panel">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Ticket</p>
                        <h2 class="font-display text-xl font-semibold text-slate-900">{{ $complaint->ticket_number }}</h2>
                        <p class="mt-1 text-sm text-slate-600">{{ $complaint->customer_name }} | {{ $complaint->brand?->name ?? 'Tanpa Brand' }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusTone($complaint->status) }}">{{ $complaint->status }}</span>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">CAPA {{ $complaint->capa_status }}</span>
                        <a href="{{ route('complaints.index') }}" class="crm-btn-secondary">Kembali</a>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 text-sm text-slate-700 sm:grid-cols-2">
                    <p><span class="font-semibold">Kategori:</span> {{ $complaint->category?->name ?? '-' }}</p>
                    <p><span class="font-semibold">Channel:</span> {{ $complaint->complaint_channel }}</p>
                    <p><span class="font-semibold">PIC:</span> {{ $complaint->assigned_to ?: '-' }}</p>
                    <p><span class="font-semibold">Kode Produksi:</span> {{ $complaint->production_code ?: '-' }}</p>
                    <p><span class="font-semibold">Target Resolve:</span> {{ $complaint->target_resolution_date?->format('d M Y') ?: '-' }}</p>
                    <p><span class="font-semibold">CAPA Due Date:</span> {{ $complaint->capa_due_date?->format('d M Y') ?: '-' }}</p>
                </div>

                <div class="mt-4 rounded-xl bg-slate-50 p-3 text-sm text-slate-700">
                    {{ $complaint->description }}
                </div>
            </article>

            @if ($canUpdateStatus)
                <article class="crm-panel">
                    <h3 class="font-display text-lg font-semibold text-slate-900">Update Status Ticket</h3>
                    <form method="POST" action="{{ route('complaints.status', $complaint) }}" class="mt-3 grid gap-3">
                        @csrf
                        @method('PATCH')
                        <input name="author" class="crm-input" value="{{ auth()->user()?->name }}" placeholder="Nama petugas">
                        <select name="status" class="crm-input" required>
                            @foreach ($statusOptions as $status)
                                @if ($status !== 'Closed')
                                    <option value="{{ $status }}" @selected($complaint->status === $status)>{{ $status }}</option>
                                @endif
                            @endforeach
                        </select>
                        <input name="assigned_to" value="{{ $complaint->assigned_to }}" class="crm-input" placeholder="PIC">
                        <input type="date" name="target_resolution_date" value="{{ $complaint->target_resolution_date?->toDateString() }}" class="crm-input">
                        <textarea name="resolution_summary" class="crm-input min-h-20" placeholder="Ringkasan penyelesaian">{{ $complaint->resolution_summary }}</textarea>
                        <textarea name="note" class="crm-input min-h-20" placeholder="Catatan perubahan status"></textarea>
                        <button class="crm-btn-primary" type="submit">Simpan Perubahan</button>
                    </form>
                </article>
            @endif

            <article class="crm-panel">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-lg font-semibold text-slate-900">Workflow CAPA</h3>
                    <span class="text-xs text-slate-500">QA -> Manager -> Closed</span>
                </div>

                @if ($canSubmitCapa)
                    <form method="POST" action="{{ route('complaints.capa.submit', $complaint) }}" class="mt-3 grid gap-3 rounded-2xl border border-slate-200 p-3">
                        @csrf
                        @method('PATCH')
                        <h4 class="font-semibold text-slate-800">Submit/Revisi CAPA (QA)</h4>
                        <textarea name="capa_root_cause" class="crm-input min-h-20" placeholder="Root cause *" required>{{ $complaint->capa_root_cause }}</textarea>
                        <textarea name="capa_corrective_action" class="crm-input min-h-20" placeholder="Corrective action *" required>{{ $complaint->capa_corrective_action }}</textarea>
                        <textarea name="capa_preventive_action" class="crm-input min-h-20" placeholder="Preventive action *" required>{{ $complaint->capa_preventive_action }}</textarea>
                        <input type="date" name="capa_due_date" class="crm-input" value="{{ $complaint->capa_due_date?->toDateString() }}" required>
                        <textarea name="note" class="crm-input min-h-16" placeholder="Catatan submit CAPA"></textarea>
                        <button class="crm-btn-primary" type="submit">Submit CAPA ke Manager</button>
                    </form>
                @endif

                @if ($canApproveCapa)
                    <form method="POST" action="{{ route('complaints.capa.approve', $complaint) }}" class="mt-3 grid gap-3 rounded-2xl border border-emerald-200 p-3">
                        @csrf
                        @method('PATCH')
                        <h4 class="font-semibold text-slate-800">Approve CAPA (Manager)</h4>
                        <textarea name="note" class="crm-input min-h-16" placeholder="Catatan approval (opsional)"></textarea>
                        <button class="crm-btn-primary" type="submit">Approve CAPA</button>
                    </form>

                    <form method="POST" action="{{ route('complaints.capa.reject', $complaint) }}" class="mt-3 grid gap-3 rounded-2xl border border-rose-200 p-3">
                        @csrf
                        @method('PATCH')
                        <h4 class="font-semibold text-slate-800">Reject CAPA (Manager)</h4>
                        <textarea name="capa_rejected_reason" class="crm-input min-h-20" placeholder="Alasan reject *" required></textarea>
                        <button class="crm-btn-secondary" type="submit">Reject CAPA</button>
                    </form>
                @endif

                @if ($canCloseCapa)
                    <form method="POST" action="{{ route('complaints.capa.close', $complaint) }}" class="mt-3 grid gap-3 rounded-2xl border border-slate-200 p-3">
                        @csrf
                        @method('PATCH')
                        <h4 class="font-semibold text-slate-800">Close Ticket</h4>
                        <textarea name="resolution_summary" class="crm-input min-h-20" placeholder="Ringkasan final penyelesaian *" required>{{ $complaint->resolution_summary }}</textarea>
                        <button class="crm-btn-primary" type="submit">Close Ticket</button>
                    </form>
                @endif
            </article>
        </div>

        <div class="space-y-4">
            @if ($canEdit)
                <article class="crm-panel">
                    <h3 class="font-display text-lg font-semibold text-slate-900">Catatan</h3>
                    <form method="POST" action="{{ route('complaints.notes', $complaint) }}" class="mt-3 grid gap-3">
                        @csrf
                        <input name="author" class="crm-input" value="{{ auth()->user()?->name }}" placeholder="Nama petugas" required>
                        <textarea name="note" class="crm-input min-h-20" placeholder="Catatan investigasi / tindak lanjut" required></textarea>
                        <button class="crm-btn-secondary" type="submit">Tambah Catatan</button>
                    </form>
                </article>
            @endif

            <article class="crm-panel">
                <h3 class="font-display text-lg font-semibold text-slate-900">Timeline</h3>
                <div class="mt-3 max-h-80 space-y-3 overflow-auto pr-1">
                    @forelse ($complaint->updates as $update)
                        <div class="rounded-2xl border border-slate-200 p-3 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-semibold text-slate-800">{{ ucfirst(str_replace('_', ' ', $update->event_type)) }}</p>
                                <p class="text-xs text-slate-500">{{ $update->event_at?->format('d M Y H:i') }}</p>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Oleh {{ $update->author ?: 'System' }}</p>
                            @if ($update->note)
                                <p class="mt-2 text-slate-700">{{ $update->note }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada timeline.</p>
                    @endforelse
                </div>
            </article>

            <article class="crm-panel">
                <h3 class="font-display text-lg font-semibold text-slate-900">Lampiran Bukti</h3>
                @if ($canEdit)
                    <form method="POST" action="{{ route('complaints.attachments.store', $complaint) }}" enctype="multipart/form-data" class="mt-3 grid gap-3">
                        @csrf
                        <input name="author" class="crm-input" value="{{ auth()->user()?->name }}" placeholder="Nama petugas">
                        <input type="file" name="files[]" class="crm-input" multiple required>
                        <button class="crm-btn-secondary" type="submit">Upload Lampiran</button>
                    </form>
                @endif

                <div class="mt-3 space-y-2">
                    @forelse ($complaint->attachments as $attachment)
                        <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2 text-sm">
                            <div>
                                <p class="font-medium text-slate-700">{{ $attachment->original_name }}</p>
                                <p class="text-xs text-slate-500">{{ number_format($attachment->size / 1024, 1) }} KB | {{ $attachment->uploaded_by }}</p>
                            </div>
                            <a class="text-cyan-700 hover:text-cyan-900" href="{{ route('complaints.attachments.download', [$complaint, $attachment]) }}">Download</a>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada lampiran.</p>
                    @endforelse
                </div>
            </article>

            <article class="crm-panel">
                <h3 class="font-display text-lg font-semibold text-slate-900">Audit Log</h3>
                <div class="mt-3 max-h-64 space-y-2 overflow-auto pr-1">
                    @forelse ($auditLogs as $log)
                        <div class="rounded-xl border border-slate-200 p-3 text-xs">
                            <p class="font-semibold text-slate-700">{{ $log->action }}</p>
                            <p class="mt-1 text-slate-500">{{ $log->created_at?->format('d M Y H:i:s') }} | {{ $log->user?->name ?? 'System' }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada audit log.</p>
                    @endforelse
                </div>
            </article>
        </div>
    </section>
@endsection
