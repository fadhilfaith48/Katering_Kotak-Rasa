@extends('layouts.app')

@section('title', 'Pesanan')

@section('content')
    <x-page-header judul="Pesanan" deskripsi="Daftar pesanan katering beserta status pengerjaannya.">
        <x-slot:aksi>
            <a href="{{ route('pesanan.create') }}"
               class="inline-flex items-center gap-1.5 rounded-xl bg-orange-600 px-3.5 py-2.5 text-sm font-bold text-white shadow-sm shadow-orange-600/30 transition hover:bg-orange-700 active:scale-[0.99]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Baru
            </a>
        </x-slot:aksi>
    </x-page-header>

    @php
        $daftarStatus = [
            '' => 'Semua',
            'baru' => 'Baru',
            'diproses' => 'Diproses',
            'selesai' => 'Selesai',
            'batal' => 'Batal',
        ];
        $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
    @endphp

    {{-- Pencarian --}}
    <form method="GET" action="{{ route('pesanan.index') }}" class="mb-3">
        @if ($status !== '')
            <input type="hidden" name="status" value="{{ $status }}">
        @endif

        <div class="flex items-center gap-2">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-9 place-items-center text-slate-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </span>
                <input type="search"
                       name="q"
                       value="{{ $cari }}"
                       placeholder="Cari nama pelanggan atau nomor"
                       class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-9 pr-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/25">
            </div>

            <button type="submit"
                    class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                Cari
            </button>
        </div>
    </form>

    {{-- Filter status --}}
    <div class="mb-4 flex gap-2 overflow-x-auto pb-1">
        @foreach ($daftarStatus as $nilai => $label)
            @php
                $aktif = $status === $nilai;
                $tautan = route('pesanan.index', array_filter([
                    'status' => $nilai !== '' ? $nilai : null,
                    'q' => $cari !== '' ? $cari : null,
                ]));
            @endphp

            <a href="{{ $tautan }}"
               class="shrink-0 rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $aktif ? 'border-orange-600 bg-orange-600 text-white' : 'border-slate-200 bg-white text-slate-500 hover:border-orange-200 hover:text-orange-600' }}"
               @if ($aktif) aria-current="page" @endif>
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Daftar --}}
    @forelse ($pesanan as $item)
        <a href="{{ route('pesanan.show', $item) }}"
           class="mb-3 block rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-orange-200 hover:shadow-md active:scale-[0.995]">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ $item->nomor }}</p>
                    <p class="mt-0.5 truncate text-sm font-bold text-slate-900">
                        {{ $item->pelanggan?->nama ?? 'Pelanggan' }}
                    </p>
                </div>

                <x-status-pesanan :status="$item->status" class="shrink-0" />
            </div>

            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-[11px] text-slate-500">
                <span class="inline-flex items-center gap-1">
                    <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    {{ $item->tanggal_acara?->locale('id')->isoFormat('ddd, D MMM YYYY') }}
                </span>

                <span>{{ $item->jumlah_box }} box</span>

                <span class="inline-flex items-center gap-1">
                    <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                    {{ $item->jalur_pesanan === 'whatsapp' ? 'WhatsApp' : 'Langsung' }}
                </span>
            </div>

            <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-2.5">
                <x-status-bayar :status="$item->status_bayar" />

                <span class="text-sm font-bold text-slate-900">{{ $rupiah($item->total) }}</span>
            </div>
        </a>
    @empty
        <x-empty-state judul="Belum ada pesanan" deskripsi="Pesanan yang kamu catat akan muncul di sini.">
            <a href="{{ route('pesanan.create') }}"
               class="inline-flex items-center gap-1.5 rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-600/25 transition hover:bg-orange-700">
                Buat Pesanan Pertama
            </a>
        </x-empty-state>
    @endforelse

    @if ($pesanan->hasPages())
        <div class="mt-4">
            {{ $pesanan->links() }}
        </div>
    @endif
@endsection
