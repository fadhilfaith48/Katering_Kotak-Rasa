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
    <section class="mb-5">
        <div class="flex items-center justify-between gap-2">
            <span class="rounded-full bg-kr-soft px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-kr-accent">
                Ringkasan Katering Banjarsari
            </span>
            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-kr-success">
                <span class="h-1.5 w-1.5 rounded-full bg-kr-success"></span>
                Dapur aktif
            </span>
        </div>

        <h1 class="mt-2.5 text-2xl font-bold tracking-tight text-kr-ink lg:text-3xl">{{ $sapaan }}, {{ $pengguna->name }}!</h1>
        <p class="mt-1 text-sm leading-relaxed text-kr-muted">
            Berikut rangkuman pesanan dan operasional <b class="text-kr-brown">KotakRasa</b> hari ini,
            <b class="text-kr-brown">{{ $hariIni }}</b>.
        </p>
    </section>

    {{-- Kartu metrik --}}
    <section class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @if ($pengguna->isPemilik())
            <div class="col-span-2 rounded-2xl border-b-[3px] border-b-kr-accent bg-kr-primary p-4 text-white shadow-md shadow-kr-primary/25">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wide text-white/80">Omzet Bulan Ini</span>
                    <svg class="h-4 w-4 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.307a11.95 11.95 0 0 1 5.814-5.519l2.74-1.22m0 0-5.94-2.28m5.94 2.28-2.28 5.941" />
                    </svg>
                </div>
                <p class="mt-2 text-2xl font-bold tracking-tight">{{ $rupiah($omzetBulanIni) }}</p>
                <p class="mt-0.5 text-[11px] text-white/80">DP &amp; pelunasan yang masuk bulan ini</p>
            </div>
        @endif

        <div class="rounded-2xl border border-kr-line border-b-[3px] border-b-kr-primary bg-white p-4 shadow-kr-card">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wide text-kr-faint">Box Aktif</span>
                <span class="grid h-7 w-7 place-items-center rounded-lg bg-sky-50 text-sky-600">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5 12 3.75 3.75 7.5m16.5 0L12 11.25M20.25 7.5v9L12 20.25m0-9L3.75 7.5v9L12 20.25" />
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-xl font-bold text-kr-ink">{{ number_format($totalBoxAktif, 0, ',', '.') }} <span class="text-sm font-semibold text-kr-faint">box</span></p>
            <p class="mt-0.5 text-[11px] text-kr-faint">
                Perlu disiapkan &middot; {{ number_format($totalSnackAktif, 0, ',', '.') }} snack
            </p>
        </div>

        <div class="rounded-2xl border border-kr-line border-b-[3px] border-b-kr-primary bg-white p-4 shadow-kr-card">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wide text-kr-faint">Pesanan Aktif</span>
                <span class="grid h-7 w-7 place-items-center rounded-lg bg-emerald-50 text-kr-success">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-xl font-bold text-kr-ink">{{ $totalPesananAktif }} <span class="text-sm font-semibold text-kr-faint">pesanan</span></p>
            <p class="mt-0.5 text-[11px] text-kr-faint">Acara bulan ini</p>
        </div>
    </section>

    {{-- Aksi cepat --}}
    <section class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <a href="{{ route('pesanan.create') }}"
           class="group flex flex-col justify-between rounded-[16px] bg-kr-primary p-4 text-white shadow-md shadow-kr-primary/25 transition hover:bg-kr-accent active:scale-[0.99] lg:col-span-2">
            <span class="grid h-9 w-9 place-items-center rounded-xl bg-white/15">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
            </span>
            <span class="mt-5 block">
                <span class="block text-sm font-bold leading-tight">Buat Pesanan Baru</span>
                <span class="mt-0.5 block text-[11px] text-white/80">Catat pesanan &amp; menu</span>
            </span>
        </a>

        <a href="{{ route('pengeluaran.index') }}"
           class="group flex flex-col justify-between rounded-[16px] border border-kr-line bg-white p-4 text-kr-ink shadow-kr-card transition hover:border-kr-line-strong hover:bg-kr-soft/40 active:scale-[0.99] lg:col-span-2">
            <span class="grid h-9 w-9 place-items-center rounded-xl bg-kr-tint text-kr-primary">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 1 3 7.5m18 4.5v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9v3m18 0h-5.25a.75.75 0 0 0-.75.75v.5c0 .414.336.75.75.75H21" />
                </svg>
            </span>
            <span class="mt-5 block">
                <span class="block text-sm font-bold leading-tight">Catat Pengeluaran</span>
                <span class="mt-0.5 block text-[11px] text-kr-faint">Belanja &amp; operasional</span>
            </span>
        </a>
    </section>

    {{-- Pesanan terdekat --}}
    <section class="mt-5">
        <div class="overflow-hidden rounded-2xl border border-kr-line bg-white shadow-kr-card">
            <div class="flex items-start justify-between gap-2 border-b border-kr-line/70 px-4 py-3.5">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wide text-kr-faint">Operasional dapur</p>
                    <h2 class="text-sm font-bold text-kr-ink">Perlu Disiapkan Hari Ini &amp; Besok</h2>
                </div>
                <a href="{{ route('pesanan.index') }}" class="shrink-0 pt-0.5 text-[11px] font-semibold text-kr-primary hover:text-kr-accent">
                    Lihat semua
                </a>
            </div>

            @forelse ($pesananTerdekat as $pesanan)
                @php
                    $labelHari = $pesanan->tanggal_acara->isToday() ? 'Hari ini' : 'Besok';
                    $warnaStatus = match ($pesanan->status) {
                        'baru' => 'bg-sky-50 text-sky-700 ring-sky-100',
                        'diproses' => 'bg-amber-50 text-kr-warning ring-amber-100',
                        'selesai' => 'bg-emerald-50 text-kr-success-deep ring-emerald-100',
                        default => 'bg-rose-50 text-kr-danger ring-rose-100',
                    };
                @endphp

                <a href="{{ route('pesanan.show', $pesanan) }}"
                   class="flex items-center gap-3 border-b border-kr-line/70 px-4 py-3 transition last:border-b-0 hover:bg-kr-soft/80">
                    <span class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-xl bg-kr-tint text-kr-primary">
                        <span class="text-sm font-bold leading-none">{{ $pesanan->tanggal_acara->format('d') }}</span>
                        <span class="mt-0.5 text-[9px] font-bold uppercase leading-none">{{ $pesanan->tanggal_acara->locale('id')->isoFormat('MMM') }}</span>
                    </span>

                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold text-kr-ink">
                            {{ $pesanan->pelanggan?->nama ?? 'Pelanggan' }}
                        </span>
                        <span class="mt-0.5 block text-[11px] text-kr-faint">
                            {{ $labelHari }} &middot; {{ $pesanan->jumlah_box }} box
                            @if ($pesanan->jumlah_snack > 0)
                                &middot; {{ $pesanan->jumlah_snack }} snack
                            @endif
                        </span>
                    </span>

                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold capitalize ring-1 {{ $warnaStatus }}">
                        {{ $pesanan->status }}
                    </span>
                </a>
            @empty
                <div class="px-6 py-10 text-center">
                    <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-kr-soft text-kr-faint">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </span>
                    <p class="mt-3 text-sm font-semibold text-kr-muted">Belum ada pesanan</p>
                    <p class="mt-1 text-xs text-kr-faint">Pesanan untuk hari ini dan besok akan muncul di sini.</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
