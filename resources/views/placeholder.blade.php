@extends('layouts.app')

@section('title', $judul ?? 'Segera Hadir')

@section('content')
    <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center shadow-sm">
        <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-orange-50 text-orange-500">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 4.5l7.5 7.5-7.5 7.5M3 12h15.75" />
            </svg>
        </span>

        <h1 class="mt-4 text-lg font-bold text-slate-900">{{ $judul ?? 'Segera Hadir' }}</h1>
        <p class="mx-auto mt-2 max-w-xs text-sm leading-relaxed text-slate-500">
            {{ $deskripsi ?? 'Modul ini akan tersedia pada tahap pengembangan berikutnya.' }}
        </p>

        <a href="{{ route('dashboard') }}"
           class="mt-6 inline-flex items-center gap-1.5 rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-600/25 transition hover:bg-orange-700">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Beranda
        </a>
    </div>
@endsection
