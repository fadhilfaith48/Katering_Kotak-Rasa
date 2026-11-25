@extends('layouts.app')

@section('title', 'Jadwal')

@section('content')
    @php
        $labelTanggal = function (string $kunci): string {
            return $kunci === 'tanpa-tanggal'
                ? 'Tanggal belum diisi'
                : \Illuminate\Support\Carbon::parse($kunci)->locale('id')->isoFormat('dddd, D MMMM YYYY');
        };

        $jumlahBox = fn ($pesanan) => (int) $pesanan->items
            ->filter(fn ($item) => $item->menu?->jenis === 'box')
            ->sum('jumlah');

        $jumlahSnack = fn ($pesanan) => (int) $pesanan->items
            ->filter(fn ($item) => $item->menu?->jenis === 'snack')
            ->sum('jumlah');
    @endphp

    <x-page-header judul="Jadwal"
                   deskripsi="Pesanan yang akan datang berdasarkan tanggal acara, lengkap dengan jumlah box dan statusnya." />

    {{-- Filter rentang --}}
    <div class="mb-3 flex gap-2 overflow-x-auto pb-1">
        @foreach (['hari-ini' => 'Hari ini', 'minggu-ini' => 'Minggu ini', 'semua' => 'Semua'] as $nilai => $label)
            @php $aktif = $rentang === $nilai; @endphp

            <a href="{{ route('jadwal.index', ['rentang' => $nilai]) }}"
               class="shrink-0 rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $aktif ? 'border-orange-600 bg-orange-600 text-white' : 'border-slate-200 bg-white text-slate-500 hover:border-orange-200 hover:text-orange-600' }}"
               @if ($aktif) aria-current="page" @endif>
                {{ $label }}
            </a>
        @endforeach

        @if ($rentang === 'tanggal')
            <span class="shrink-0 rounded-full border border-orange-600 bg-orange-600 px-3 py-1.5 text-xs font-semibold text-white">
                Tanggal: {{ \Illuminate\Support\Carbon::parse($tanggal)->locale('id')->isoFormat('D MMM YYYY') }}
            </span>
        @endif
    </div>

    {{-- Filter tanggal tertentu --}}
    <form method="GET" action="{{ route('jadwal.index') }}"
          class="mb-4 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
        <label for="tanggal" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
            Atau pilih tanggal tertentu
        </label>

        <div class="flex items-end gap-2">
            <input id="tanggal"
                   type="date"
                   name="tanggal"
                   value="{{ $rentang === 'tanggal' ? $tanggal : '' }}"
                   class="min-w-0 flex-1 rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-500/25">

            <button type="submit"
                    class="shrink-0 rounded-xl bg-slate-800 px-3.5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-900">
                Tampilkan
            </button>
        </div>

        @if ($rentang === 'tanggal')
            <a href="{{ route('jadwal.index') }}"
               class="mt-2 inline-block text-[11px] font-semibold text-orange-600 hover:underline">
                Hapus filter tanggal
            </a>
        @endif
    </form>

    {{-- Rekap beban kerja --}}
    <div class="mb-4 rounded-2xl bg-slate-900 px-4 py-4 text-white shadow-sm">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-300">Beban kerja pada periode ini</p>
        <p class="mt-1 text-2xl font-bold tracking-tight">{{ $totalBox }} box</p>
        <p class="mt-1 text-xs text-slate-300">{{ $jumlahPesanan }} pesanan aktif &middot; {{ $totalSnack }} snack</p>

        @if ($jumlahBatal > 0)
            <p class="mt-1 text-xs text-slate-400">{{ $jumlahBatal }} pesanan batal tidak dihitung.</p>
        @endif
    </div>

    {{-- Daftar per tanggal acara --}}
    @forelse ($grup as $kunciTanggal => $daftar)
        <section class="mb-5">
            <div class="mb-2 flex items-center gap-2">
                <h2 class="text-xs font-bold uppercase tracking-wide text-slate-500">
                    {{ $labelTanggal((string) $kunciTanggal) }}
                </h2>
                <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-bold text-slate-600">
                    {{ $daftar->count() }} pesanan
                </span>
            </div>

            @foreach ($daftar as $item)
                @php
                    $batal = $item->status === 'batal';
                    $box = $jumlahBox($item);
                    $snack = $jumlahSnack($item);
                @endphp

                <a href="{{ route('pesanan.show', $item) }}"
                   class="mb-2 block rounded-2xl border p-4 shadow-sm transition hover:border-orange-200 hover:shadow-md active:scale-[0.995] {{ $batal ? 'border-slate-200 bg-slate-50 opacity-70' : 'border-slate-200 bg-white' }}">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ $item->nomor }}</p>
                            <p class="mt-0.5 truncate text-sm font-bold {{ $batal ? 'text-slate-500 line-through' : 'text-slate-900' }}">
                                {{ $item->pelanggan?->nama ?? 'Pelanggan' }}
                            </p>
                        </div>

                        <div class="flex shrink-0 items-center gap-1.5">
                            @if ($batal)
                                <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-bold text-slate-600">
                                    Tidak dihitung
                                </span>
                            @endif
                            <x-status-pesanan :status="$item->status" />
                        </div>
                    </div>

                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-[11px] text-slate-500">
                        <span class="font-semibold {{ $batal ? 'text-slate-400' : 'text-orange-600' }}">
                            {{ $box }} box
                        </span>
                        @if ($snack > 0)
                            <span>{{ $snack }} snack</span>
                        @endif
                        <span>{{ $item->jalur_pesanan === 'whatsapp' ? 'WhatsApp' : 'Langsung' }}</span>
                    </div>

                    <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-2.5">
                        <x-status-bayar :status="$item->status_bayar" />

                        <span class="text-[11px] font-bold text-orange-600">Detail &rarr;</span>
                    </div>
                </a>
            @endforeach
        </section>
    @empty
        <x-empty-state judul="Belum ada jadwal"
                       deskripsi="Tidak ada pesanan pada periode ini. Coba rentang lain atau tambah pesanan baru.">
            <a href="{{ route('pesanan.create') }}"
               class="inline-flex items-center gap-1.5 rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-600/25 transition hover:bg-orange-700">
                Buat Pesanan Baru
            </a>
        </x-empty-state>
    @endforelse
@endsection
