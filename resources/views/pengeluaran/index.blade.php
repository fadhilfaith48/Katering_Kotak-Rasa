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
               class="inline-flex items-center gap-1.5 rounded-xl bg-orange-600 px-3.5 py-2.5 text-sm font-bold text-white shadow-sm shadow-orange-600/30 transition hover:bg-orange-700 active:scale-[0.99]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Baru
            </a>
        </x-slot:aksi>
    </x-page-header>

    {{-- Filter bulan --}}
    <form method="GET" action="{{ route('pengeluaran.index') }}"
          class="mb-4 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
        <label for="bulan" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
            Filter bulan
        </label>

        <div class="flex items-end gap-2">
            <input id="bulan"
                   type="month"
                   name="bulan"
                   value="{{ $bulan }}"
                   class="min-w-0 flex-1 rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-500/25">

            <button type="submit"
                    class="shrink-0 rounded-xl bg-slate-800 px-3.5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-900">
                Tampilkan
            </button>
        </div>

        <a href="{{ route('pengeluaran.index', ['bulan' => 'all']) }}"
           class="mt-2 inline-block text-[11px] font-semibold text-orange-600 hover:underline">
            Lihat semua bulan
        </a>
    </form>

    {{-- Ringkasan hanya untuk Pemilik --}}
    @if ($pengguna->isPemilik())
        <div class="mb-4 rounded-2xl bg-slate-900 px-4 py-4 text-white shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-300">Total pengeluaran</p>
            <p class="mt-1 text-2xl font-bold tracking-tight">{{ $rupiah($totalBulanIni) }}</p>
            <p class="mt-1 text-xs text-slate-300">{{ $jumlahTransaksi }} transaksi pada periode ini</p>
        </div>
    @endif

    {{-- Daftar --}}
    <div class="lg:grid lg:grid-cols-2 lg:items-start lg:gap-4">
    @forelse ($pengeluaran as $item)
        <div class="mb-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:mb-0">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold text-slate-900">{{ $item->keterangan }}</p>
                    <p class="mt-0.5 text-[11px] text-slate-400">
                        {{ $item->tanggal?->locale('id')->isoFormat('ddd, D MMM YYYY') }}
                    </p>
                </div>

                <p class="shrink-0 text-sm font-bold text-rose-600">-{{ $rupiah($item->nominal) }}</p>
            </div>

            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-2.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600">
                        {{ $item->kategori?->nama_kategori ?? 'Tanpa kategori' }}
                    </span>
                    <span class="text-[10px] text-slate-400">oleh {{ $item->pencatat?->name ?? '-' }}</span>
                </div>

                @if ($pengguna->isPemilik())
                    <div class="flex items-center gap-2">
                        <a href="{{ route('pengeluaran.edit', $item) }}"
                           class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-600 transition hover:border-orange-200 hover:text-orange-600">
                            Ubah
                        </a>

                        <form method="POST"
                              action="{{ route('pengeluaran.destroy', $item) }}"
                              onsubmit="return confirm('Hapus pengeluaran ini? Tindakan ini tidak dapat dibatalkan.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="rounded-lg border border-rose-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-rose-600 transition hover:bg-rose-50">
                                Hapus
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="lg:col-span-2">
            <x-empty-state judul="Belum ada pengeluaran"
                           deskripsi="Pengeluaran yang kamu catat pada bulan ini akan muncul di sini.">
                <a href="{{ route('pengeluaran.create') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-600/25 transition hover:bg-orange-700">
                    Catat Pengeluaran Pertama
                </a>
            </x-empty-state>
        </div>
    @endforelse
    </div>

    @if ($pengeluaran->hasPages())
        <div class="mt-4">
            {{ $pengeluaran->links() }}
        </div>
    @endif
@endsection
