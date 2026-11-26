@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
    @php
        $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
    @endphp

    <x-page-header judul="Laporan"
                   deskripsi="Pemasukan, pengeluaran, laba bersih, dan tagihan yang belum lunas." />

    {{-- Filter bulan --}}
    <form method="GET" action="{{ route('laporan.index') }}"
          class="mb-4 rounded-2xl border border-kr-line bg-white p-3 shadow-kr-card lg:max-w-xl">
        <label for="bulan" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-kr-muted">
            Bulan laporan
        </label>

        <div class="flex items-end gap-2">
            <input id="bulan"
                   type="month"
                   name="bulan"
                   value="{{ $bulan }}"
                   class="min-w-0 flex-1 rounded-xl border border-kr-line-strong bg-white px-3 py-3 text-base text-kr-ink outline-none transition focus:border-kr-primary focus:ring-2 focus:ring-kr-primary/25">

            <button type="submit"
                    class="shrink-0 min-h-12 rounded-[14px] bg-kr-primary px-4 py-3 text-base font-bold text-white transition hover:bg-kr-accent">
                Tampilkan
            </button>
        </div>
    </form>

    {{-- Ekspor laporan --}}
    <div class="mb-4 flex flex-col gap-2 rounded-2xl border border-kr-line bg-white p-3 shadow-kr-card sm:flex-row sm:items-center sm:justify-between lg:max-w-xl">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-wide text-kr-muted">Unduh laporan</p>
            <p class="mt-0.5 text-[10px] text-kr-faint">{{ $labelBulan }} &middot; siap dibuka di Excel atau dicetak</p>
        </div>

        <div class="grid grid-cols-2 gap-2 sm:flex">
            <a href="{{ route('laporan.export.excel', ['bulan' => $bulan]) }}"
               class="inline-flex min-h-12 items-center justify-center gap-1.5 rounded-[14px] border border-kr-line-strong bg-white px-3 py-2.5 text-sm font-bold text-kr-brown transition hover:bg-kr-soft active:scale-[0.99]">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M4.5 16.5v1.5A2.25 2.25 0 0 0 6.75 20.25h10.5A2.25 2.25 0 0 0 19.5 18v-1.5" />
                </svg>
                Excel
            </a>

            <a href="{{ route('laporan.export.pdf', ['bulan' => $bulan]) }}"
               class="inline-flex min-h-12 items-center justify-center gap-1.5 rounded-[14px] border border-kr-line-strong bg-white px-3 py-2.5 text-sm font-bold text-kr-brown transition hover:bg-kr-soft active:scale-[0.99]">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M4.5 16.5v1.5A2.25 2.25 0 0 0 6.75 20.25h10.5A2.25 2.25 0 0 0 19.5 18v-1.5" />
                </svg>
                PDF
            </a>
        </div>
    </div>

    {{-- Ringkasan AI --}}
    @php
        $ringkasanAi = session('ringkasan_ai');
        $ringkasanAiAktif = is_array($ringkasanAi) && ($ringkasanAi['bulan'] ?? null) === $bulan;
    @endphp

    <section class="mb-4 rounded-2xl border border-kr-line bg-white p-3 shadow-kr-card lg:max-w-xl">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wide text-kr-muted">Ringkasan AI</p>
                <p class="mt-0.5 text-[10px] text-kr-faint">Ringkas angka {{ $labelBulan }} jadi beberapa kalimat.</p>
            </div>

            @if ($ringkasanAiAktif)
                <span class="shrink-0 rounded-full bg-kr-soft px-2 py-0.5 text-[10px] font-bold text-kr-muted">
                    Dibuat oleh AI
                </span>
            @endif
        </div>

        @if ($ringkasanAiAktif)
            <p class="mt-2 whitespace-pre-line text-xs leading-relaxed text-kr-brown">{{ $ringkasanAi['teks'] }}</p>
            <p class="mt-1.5 text-[10px] text-kr-faint">Dibuat {{ $ringkasanAi['waktu'] }} dari angka laporan.</p>
        @endif

        @if (session('ringkasan_ai_error'))
            <p class="mt-2 rounded-xl bg-amber-50 px-3 py-2 text-[11px] text-kr-warning">
                {{ session('ringkasan_ai_error') }}
            </p>
        @endif

        <form method="POST" action="{{ route('laporan.ringkasan-ai') }}" class="mt-2">
            @csrf
            <input type="hidden" name="bulan" value="{{ $bulan }}">
            <button type="submit"
                    class="inline-flex min-h-10 items-center justify-center rounded-[14px] border border-kr-line-strong bg-white px-3 py-2 text-xs font-bold text-kr-brown transition hover:bg-kr-soft active:scale-[0.99]">
                {{ $ringkasanAiAktif ? 'Buat ulang ringkasan' : 'Buat ringkasan' }}
            </button>
        </form>
    </section>

    {{-- Ringkasan keuangan --}}
    <section class="mb-5 rounded-2xl bg-kr-ink px-4 py-4 text-white shadow-kr-card lg:px-6 lg:py-5">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-white/70">Pemasukan &middot; {{ $labelBulan }}</p>
        <p class="mt-1 text-2xl font-bold tracking-tight">{{ $rupiah($pemasukan) }}</p>

        <div class="mt-3 grid grid-cols-2 gap-3 border-t border-white/10 pt-3">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-wide text-white/60">Total pengeluaran</p>
                <p class="mt-0.5 text-sm font-bold text-rose-300">{{ $rupiah($totalPengeluaran) }}</p>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-wide text-white/60">
                    {{ $laba >= 0 ? 'Laba bersih' : 'Rugi' }}
                </p>
                <p class="mt-0.5 text-sm font-bold {{ $laba >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">
                    {{ $rupiah(abs($laba)) }}
                </p>
            </div>
        </div>

        @if ($dpHangus > 0)
            <p class="mt-3 text-[11px] text-white/60">
                Termasuk DP hangus {{ $rupiah($dpHangus) }}.
            </p>
        @endif
    </section>

    <div class="lg:grid lg:grid-cols-2 lg:items-start lg:gap-5">
    {{-- Rincian pengeluaran per kategori --}}
    <section class="mb-5 rounded-2xl border border-kr-line bg-white p-4 shadow-kr-card lg:mb-0">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-kr-ink">Pengeluaran per kategori</h2>
                <p class="mt-0.5 text-[11px] text-kr-faint">{{ $jumlahTransaksi }} transaksi pada bulan ini</p>
            </div>
            <span class="shrink-0 rounded-full bg-kr-soft px-2 py-0.5 text-[10px] font-bold text-kr-muted">
                {{ $perKategori->count() }} kategori
            </span>
        </div>

        @forelse ($perKategori as $item)
            @php
                $persen = $totalPengeluaran > 0
                    ? (int) round(((float) $item->total / $totalPengeluaran) * 100)
                    : 0;
            @endphp

            <div class="mt-3 border-t border-kr-line/70 pt-3 first:mt-3">
                <div class="flex items-center justify-between gap-3">
                    <p class="truncate text-sm font-semibold text-kr-brown">
                        {{ $item->kategori?->nama_kategori ?? 'Tanpa kategori' }}
                    </p>
                    <p class="shrink-0 text-sm font-bold text-kr-ink">{{ $rupiah($item->total) }}</p>
                </div>
                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-kr-soft">
                    <div class="h-full rounded-full bg-kr-primary" style="width: {{ $persen }}%"></div>
                </div>
                <p class="mt-1 text-[10px] text-kr-faint">{{ $persen }}% dari total pengeluaran</p>
            </div>
        @empty
            <p class="mt-3 border-t border-kr-line/70 pt-3 text-xs text-kr-faint">
                Belum ada pengeluaran pada bulan ini.
            </p>
        @endforelse
    </section>

    {{-- Tagihan belum lunas --}}
    <section>
        <div class="mb-2 flex items-center justify-between gap-3">
            <h2 class="text-sm font-bold text-kr-ink">Tagihan belum lunas</h2>
            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-kr-warning">
                {{ $tagihan->count() }} pesanan
            </span>
        </div>

        <div class="mb-3 rounded-2xl border border-kr-line bg-kr-tint px-4 py-3">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-kr-accent/80">Total tagihan</p>
            <p class="mt-0.5 text-xl font-bold tracking-tight text-kr-accent">{{ $rupiah($totalTagihan) }}</p>
            <p class="mt-1 text-[10px] text-kr-accent/70">Pesanan batal tidak dihitung.</p>
        </div>

        @forelse ($tagihan as $item)
            <a href="{{ route('pesanan.show', $item) }}"
               class="mb-2 block rounded-2xl border border-kr-line bg-white p-4 shadow-kr-card transition hover:border-kr-line-strong hover:shadow-md active:scale-[0.995]">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-kr-faint">{{ $item->nomor }}</p>
                        <p class="mt-0.5 truncate text-sm font-bold text-kr-ink">
                            {{ $item->pelanggan?->nama ?? 'Pelanggan' }}
                        </p>
                        <p class="mt-0.5 text-[11px] text-kr-faint">
                            {{ $item->tanggal_acara?->locale('id')->isoFormat('ddd, D MMM YYYY') }}
                        </p>
                    </div>
                    <x-status-bayar :status="$item->status_bayar" class="shrink-0" />
                </div>

                <div class="mt-3 flex items-center justify-between border-t border-kr-line/70 pt-2.5">
                    <span class="text-[11px] text-kr-muted">Sisa tagihan</span>
                    <span class="text-sm font-bold text-kr-primary">{{ $rupiah($item->sisa_tagihan) }}</span>
                </div>
            </a>
        @empty
            <x-empty-state judul="Semua tagihan lunas"
                           deskripsi="Tidak ada pesanan aktif dengan tagihan yang belum dibayar.">
                <a href="{{ route('pesanan.index') }}"
                   class="inline-flex min-h-12 items-center gap-1.5 rounded-[14px] bg-kr-primary px-4 py-3 text-base font-bold text-white shadow-kr-primary/25 transition hover:bg-kr-accent">
                    Lihat Pesanan
                </a>
            </x-empty-state>
        @endforelse
    </section>
    </div>
@endsection
