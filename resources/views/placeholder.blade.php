@extends('layouts.app')

@section('title', $judul ?? 'Segera Hadir')

@section('content')
    <div class="mt-6 rounded-2xl border border-dashed border-kr-line-strong bg-white px-6 py-12 text-center shadow-kr-card">
        <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-kr-soft text-kr-primary">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 4.5l7.5 7.5-7.5 7.5M3 12h15.75" />
            </svg>
        </span>

        <h1 class="mt-4 text-lg font-bold text-kr-ink">{{ $judul ?? 'Segera Hadir' }}</h1>
        <p class="mx-auto mt-2 max-w-xs text-sm leading-relaxed text-kr-muted">
            {{ $deskripsi ?? 'Modul ini akan tersedia pada tahap pengembangan berikutnya.' }}
        </p>

        <a href="{{ route('dashboard') }}"
           class="mt-6 inline-flex min-h-12 items-center gap-1.5 rounded-[14px] bg-kr-primary px-4 py-3 text-base font-bold text-white shadow-kr-primary/25 transition hover:bg-kr-accent">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Beranda
        </a>
    </div>
@endsection
