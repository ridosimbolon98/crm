@extends('layouts.app')

@section('title', 'Panduan Sistem')

@section('content')
    <section class="mx-auto max-w-4xl">
        <article class="crm-panel">
            <h2 class="font-display text-xl font-semibold text-slate-900">Panduan Penggunaan Sistem CRM Complaint</h2>
            <p class="mt-2 text-sm text-slate-600">
                Panduan dapat dibaca online atau diunduh dalam format PDF.
            </p>

            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('guide.pdf') }}" target="_blank" class="crm-btn-primary">Buka PDF</a>
                <a href="{{ route('guide.pdf') }}" download class="crm-btn-secondary">Unduh PDF</a>
            </div>

            <div class="mt-4 rounded-2xl border border-slate-200">
                <iframe
                    src="{{ route('guide.pdf') }}"
                    title="Panduan CRM Complaint"
                    class="h-[75vh] w-full rounded-2xl"
                ></iframe>
            </div>
        </article>
    </section>
@endsection
