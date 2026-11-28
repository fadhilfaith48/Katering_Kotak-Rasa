@extends('layouts.app')

@section('title', 'Pengeluaran')

@section('content')
    @php
        $pengguna = auth()->user();
        $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
    @endphp

    <x-page-header judul="Pengeluaran" deskripsi="Daftar uang keluar usaha per kategori.">
        <x-slot:aksi>
            <a href="{{ route('pengeluaran.create') }}"
               class="inline-flex min-h-12 items-center gap-1.5 rounded-kr-sm bg-kr-primary px-4 py-3 text-base font-bold text-white shadow-kr-raised transition hover:bg-kr-accent active:scale-[0.99]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Baru
            </a>
        </x-slot:aksi>
    </x-page-header>

    {{-- Filter bulan & ringkasan --}}
    <div class="mb-4 grid gap-4 {{ $pengguna->isPemilik() ? 'lg:grid-cols-2 lg:items-start' : 'lg:max-w-md' }}">
    <form method="GET" action="{{ route('pengeluaran.index') }}"
          class="rounded-kr-md border border-kr-line bg-white p-3 shadow-kr-card">
        <label for="bulan" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-kr-muted">
            Filter bulan
        </label>

        <div class="flex items-end gap-2">
            <input id="bulan"
                   type="month"
                   name="bulan"
                   value="{{ $bulan }}"
                   class="min-w-0 flex-1 rounded-kr-sm border border-kr-line-strong bg-white px-3 py-3 text-base text-kr-ink outline-none transition focus:border-kr-primary focus:ring-2 focus:ring-kr-primary/25">

            <button type="submit"
                    class="shrink-0 min-h-12 rounded-kr-sm bg-kr-primary px-4 py-3 text-base font-bold text-white transition hover:bg-kr-accent">
                Tampilkan
            </button>
        </div>

        <a href="{{ route('pengeluaran.index', ['bulan' => 'all']) }}"
           class="mt-2 inline-block text-[11px] font-semibold text-kr-primary hover:underline">
            Lihat semua bulan
        </a>
    </form>

    {{-- Ringkasan hanya untuk Pemilik --}}
    @if ($pengguna->isPemilik())
        <div class="rounded-kr-md bg-kr-ink px-4 py-4 text-white shadow-kr-card">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-white/70">Total pengeluaran</p>
            <p class="mt-1 text-2xl font-bold tracking-tight tabular-nums">{{ $rupiah($totalBulanIni) }}</p>
            <p class="mt-1 text-xs text-white/70">{{ $jumlahTransaksi }} transaksi pada periode ini</p>
        </div>
    @endif
    </div>

    {{-- Daftar --}}
    <section class="overflow-hidden rounded-kr-md border border-kr-line bg-white shadow-kr-card">
        <div class="flex items-start justify-between gap-3 border-b border-kr-line/70 px-4 py-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wide text-kr-faint">Buku pengeluaran</p>
                <h2 class="mt-0.5 text-base font-bold text-kr-ink">
                    {{ $pengeluaran->total() }} transaksi
                </h2>
            </div>

            <a href="{{ route('pengeluaran.create') }}"
               class="shrink-0 rounded-kr-sm border border-kr-line bg-white px-3 py-1.5 text-xs font-bold text-kr-muted transition hover:border-kr-line-strong hover:text-kr-primary">
                + Catat
            </a>
        </div>

        @if ($pengeluaran->isEmpty())
            <div class="p-4">
                <x-empty-state judul="Belum ada pengeluaran"
                               deskripsi="Pengeluaran yang kamu catat pada bulan ini akan muncul di sini.">
                    <a href="{{ route('pengeluaran.create') }}"
                       class="inline-flex min-h-12 items-center gap-1.5 rounded-kr-sm bg-kr-primary px-4 py-3 text-base font-bold text-white shadow-kr-raised transition hover:bg-kr-accent">
                        Catat Pengeluaran Pertama
                    </a>
                </x-empty-state>
            </div>
        @else
            <div class="kr-table-wrap">
                <table class="kr-table kr-table--fixed">
                    <colgroup>
                        <col>
                        <col class="w-28">
                        <col class="w-36">
                        <col class="w-24">
                        <col class="w-32">
                        @if ($pengguna->isPemilik())
                            <col class="w-28">
                        @endif
                    </colgroup>
                    <thead>
                        <tr>
                            <th scope="col">Keterangan</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Kategori</th>
                            <th scope="col">Pencatat</th>
                            <th scope="col" class="num">Nominal</th>
                            @if ($pengguna->isPemilik())
                                <th scope="col"><span class="sr-only">Aksi</span></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pengeluaran as $item)
                            <tr>
                                <td data-label="Keterangan" class="cell-title">
                                    <span class="block text-sm font-bold text-kr-ink">{{ $item->keterangan }}</span>
                                </td>

                                <td data-label="Tanggal" class="whitespace-nowrap">
                                    {{ $item->tanggal?->locale('id')->isoFormat('D MMM YYYY') }}
                                </td>

                                <td data-label="Kategori">
                                    <span class="inline-flex items-center rounded-full bg-kr-soft px-2.5 py-1 text-xs font-bold text-kr-muted">
                                        {{ $item->kategori?->nama_kategori ?? 'Tanpa kategori' }}
                                    </span>
                                </td>

                                <td data-label="Pencatat" class="whitespace-nowrap">
                                    {{ $item->pencatat?->name ?? '-' }}
                                </td>

                                <td data-label="Nominal" class="num strong text-kr-danger">
                                    -{{ $rupiah($item->nominal) }}
                                </td>

                                @if ($pengguna->isPemilik())
                                    <td class="cell-actions" data-label="">
                                        <div class="flex flex-wrap items-center justify-end gap-1.5 lg:flex-col lg:items-end lg:gap-1">
                                            <a href="{{ route('pengeluaran.edit', $item) }}"
                                               class="inline-flex min-h-9 items-center rounded-kr-sm border border-kr-line bg-white px-2.5 py-1.5 text-xs font-semibold text-kr-muted transition hover:border-kr-line-strong hover:text-kr-primary">
                                                Ubah
                                            </a>

                                            <form method="POST"
                                                  action="{{ route('pengeluaran.destroy', $item) }}"
                                                  onsubmit="return confirm('Hapus pengeluaran ini? Tindakan ini tidak dapat dibatalkan.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex min-h-9 items-center rounded-kr-sm border border-kr-danger/20 bg-white px-2.5 py-1.5 text-xs font-semibold text-kr-danger transition hover:bg-kr-danger-soft">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    @if ($pengeluaran->hasPages())
        <div class="mt-4">
            {{ $pengeluaran->links() }}
        </div>
    @endif
@endsection
