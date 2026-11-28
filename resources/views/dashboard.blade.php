@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    @php
        $pengguna = auth()->user();
        $jam = (int) now()->format('G');
        $sapaan = match (true) {
            $jam < 11 => 'Selamat pagi',
            $jam < 15 => 'Selamat siang',
            $jam < 19 => 'Selamat sore',
            default => 'Selamat malam',
        };
        $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
        $hariIni = now()->locale('id')->isoFormat('dddd, D MMMM YYYY');
    @endphp

    {{-- Sapaan --}}
    <section class="mb-6">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <span class="rounded-full bg-kr-soft px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-kr-accent">
                Ringkasan Katering Banjarsari
            </span>
            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-kr-success">
                <span class="h-1.5 w-1.5 rounded-full bg-kr-success"></span>
                Dapur aktif
            </span>
        </div>

        <h1 class="mt-3 text-2xl font-bold tracking-tight text-kr-ink sm:text-3xl">{{ $sapaan }}, {{ $pengguna->name }}!</h1>
        <p class="mt-1.5 text-sm leading-relaxed text-kr-muted">
            Berikut rangkuman pesanan dan operasional <b class="font-semibold text-kr-brown">KotakRasa</b> hari ini,
            <b class="font-semibold text-kr-brown">{{ $hariIni }}</b>.
        </p>
    </section>

    {{-- Kartu metrik --}}
    <section class="grid grid-cols-2 gap-3 {{ $pengguna->isPemilik() ? 'lg:grid-cols-4' : 'lg:grid-cols-2' }} lg:gap-4">
        @if ($pengguna->isPemilik())
            <div class="col-span-2 rounded-kr-md border border-kr-line border-b-[3px] border-b-kr-primary bg-white p-4 shadow-kr-card">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wide text-kr-faint">Omzet Bulan Ini</span>
                    <span class="grid h-8 w-8 place-items-center rounded-kr-sm bg-kr-soft text-kr-primary">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.307a11.95 11.95 0 0 1 5.814-5.519l2.74-1.22m0 0-5.94-2.28m5.94 2.28-2.28 5.941" />
                        </svg>
                    </span>
                </div>
                <p class="mt-2 text-2xl font-bold tracking-tight text-kr-accent tabular-nums">{{ $rupiah($omzetBulanIni) }}</p>
                <p class="mt-1 text-xs text-kr-faint">DP &amp; pelunasan yang masuk bulan ini</p>
            </div>
        @endif

        <div class="rounded-kr-md border border-kr-line bg-white p-4 shadow-kr-card">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wide text-kr-faint">Box Aktif</span>
                <span class="grid h-8 w-8 place-items-center rounded-kr-sm bg-kr-tint text-kr-primary">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5 12 3.75 3.75 7.5m16.5 0L12 11.25M20.25 7.5v9L12 20.25m0-9L3.75 7.5v9L12 20.25" />
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-xl font-bold text-kr-ink tabular-nums">{{ number_format($totalBoxAktif, 0, ',', '.') }} <span class="text-sm font-semibold text-kr-faint">box</span></p>
            <p class="mt-1 text-xs text-kr-faint">
                Perlu disiapkan &middot; {{ number_format($totalSnackAktif, 0, ',', '.') }} snack
            </p>
        </div>

        <div class="rounded-kr-md border border-kr-line bg-white p-4 shadow-kr-card">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wide text-kr-faint">Pesanan Aktif</span>
                <span class="grid h-8 w-8 place-items-center rounded-kr-sm bg-kr-success-soft text-kr-success-deep">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-xl font-bold text-kr-ink tabular-nums">{{ $totalPesananAktif }} <span class="text-sm font-semibold text-kr-faint">pesanan</span></p>
            <p class="mt-1 text-xs text-kr-faint">Acara bulan ini</p>
        </div>
    </section>

    {{-- Aksi cepat --}}
    <section class="mt-4 grid grid-cols-2 gap-3 lg:mt-5 lg:grid-cols-4 lg:gap-4">
        <a href="{{ route('pesanan.create') }}"
           class="group flex flex-col justify-between rounded-kr-md bg-kr-primary p-4 text-white shadow-kr-raised transition hover:bg-kr-accent active:scale-[0.99] lg:col-span-2">
            <span class="grid h-10 w-10 place-items-center rounded-kr-sm bg-white/15">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
            </span>
            <span class="mt-6 block">
                <span class="block text-sm font-bold leading-tight">Buat Pesanan Baru</span>
                <span class="mt-1 block text-xs text-white/80">Catat pesanan &amp; menu</span>
            </span>
        </a>

        <a href="{{ route('pengeluaran.index') }}"
           class="group flex flex-col justify-between rounded-kr-md border border-kr-line bg-white p-4 text-kr-ink shadow-kr-card transition hover:border-kr-primary/30 hover:bg-kr-soft/50 active:scale-[0.99] lg:col-span-2">
            <span class="grid h-10 w-10 place-items-center rounded-kr-sm bg-kr-tint text-kr-primary">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 1 3 7.5m18 4.5v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9v3m18 0h-5.25a.75.75 0 0 0-.75.75v.5c0 .414.336.75.75.75H21" />
                </svg>
            </span>
            <span class="mt-6 block">
                <span class="block text-sm font-bold leading-tight">Catat Pengeluaran</span>
                <span class="mt-1 block text-xs text-kr-faint">Belanja &amp; operasional</span>
            </span>
        </a>
    </section>

    {{-- Pesanan terdekat --}}
    <section class="mt-4 lg:mt-5">
        <div class="overflow-hidden rounded-kr-md border border-kr-line bg-white shadow-kr-card">
            <div class="flex items-start justify-between gap-3 border-b border-kr-line/70 px-4 py-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wide text-kr-faint">Operasional dapur</p>
                    <h2 class="mt-0.5 text-base font-bold text-kr-ink">Perlu Disiapkan Hari Ini &amp; Besok</h2>
                </div>
                <a href="{{ route('pesanan.index') }}" class="shrink-0 pt-0.5 text-xs font-semibold text-kr-primary hover:text-kr-accent">
                    Lihat semua
                </a>
            </div>

            @forelse ($pesananTerdekat as $pesanan)
                @php
                    $labelHari = $pesanan->tanggal_acara->isToday() ? 'Hari ini' : 'Besok';
                @endphp

                <a href="{{ route('pesanan.show', $pesanan) }}"
                   class="flex items-center gap-3 border-b border-kr-line/70 px-4 py-3.5 transition last:border-b-0 hover:bg-kr-soft/60">
                    <span class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-kr-sm bg-kr-tint text-kr-primary">
                        <span class="text-base font-bold leading-none">{{ $pesanan->tanggal_acara->format('d') }}</span>
                        <span class="mt-1 text-[11px] font-bold uppercase leading-none">{{ $pesanan->tanggal_acara->locale('id')->isoFormat('MMM') }}</span>
                    </span>

                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold text-kr-ink">
                            {{ $pesanan->pelanggan?->nama ?? 'Pelanggan' }}
                        </span>
                        <span class="mt-0.5 block text-xs text-kr-faint">
                            {{ $labelHari }} &middot; {{ $pesanan->jumlah_box }} box
                            @if ($pesanan->jumlah_snack > 0)
                                &middot; {{ $pesanan->jumlah_snack }} snack
                            @endif
                        </span>
                    </span>

                    <x-status-pesanan :status="$pesanan->status" class="shrink-0" />
                </a>
            @empty
                <div class="px-6 py-12 text-center">
                    <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-kr-soft text-kr-faint">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </span>
                    <p class="mt-4 text-sm font-semibold text-kr-ink">Belum ada pesanan</p>
                    <p class="mt-1.5 text-sm text-kr-faint">Pesanan untuk hari ini dan besok akan muncul di sini.</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
