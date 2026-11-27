@extends('layouts.app')

@section('title', 'Jadwal')

@section('content')
    @php
        $labelTanggal = function (string $kunci): string {
            return $kunci === 'tanpa-tanggal'
                ? 'Tanggal belum diisi'
                : \Illuminate\Support\Carbon::parse($kunci)->locale('id')->isoFormat('dddd, D MMMM YYYY');
        };

    @endphp

    <x-page-header judul="Jadwal"
                   deskripsi="Pesanan yang akan datang berdasarkan tanggal acara, lengkap dengan jumlah box dan statusnya." />

    {{-- Filter rentang --}}
    <div class="mb-3 flex gap-2 overflow-x-auto pb-1">
        @foreach (['hari-ini' => 'Hari ini', 'minggu-ini' => 'Minggu ini', 'semua' => 'Semua'] as $nilai => $label)
            @php $aktif = $rentang === $nilai; @endphp

            <a href="{{ route('jadwal.index', ['rentang' => $nilai]) }}"
               class="shrink-0 rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $aktif ? 'border-kr-primary bg-kr-primary text-white' : 'border-kr-line bg-white text-kr-muted hover:border-kr-line-strong hover:text-kr-primary' }}"
               @if ($aktif) aria-current="page" @endif>
                {{ $label }}
            </a>
        @endforeach

        @if ($rentang === 'tanggal')
            <span class="shrink-0 rounded-full border border-kr-primary bg-kr-primary px-3 py-1.5 text-xs font-semibold text-white">
                Tanggal: {{ \Illuminate\Support\Carbon::parse($tanggal)->locale('id')->isoFormat('D MMM YYYY') }}
            </span>
        @endif
    </div>

    {{-- Filter tanggal tertentu --}}
    <form method="GET" action="{{ route('jadwal.index') }}"
          class="mb-4 rounded-kr-md border border-kr-line bg-white p-3 shadow-kr-card">
        <label for="tanggal" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-kr-muted">
            Atau pilih tanggal tertentu
        </label>

        <div class="flex items-end gap-2">
            <input id="tanggal"
                   type="date"
                   name="tanggal"
                   value="{{ $rentang === 'tanggal' ? $tanggal : '' }}"
                   class="min-w-0 flex-1 rounded-kr-sm border border-kr-line-strong bg-white px-3 py-3 text-base text-kr-ink outline-none transition focus:border-kr-primary focus:ring-2 focus:ring-kr-primary/25">

            <button type="submit"
                    class="shrink-0 min-h-12 rounded-kr-sm bg-kr-primary px-4 py-3 text-base font-bold text-white transition hover:bg-kr-accent">
                Tampilkan
            </button>
        </div>

        @if ($rentang === 'tanggal')
            <a href="{{ route('jadwal.index') }}"
               class="mt-2 inline-block text-[11px] font-semibold text-kr-primary hover:underline">
                Hapus filter tanggal
            </a>
        @endif
    </form>

    {{-- Rekap beban kerja --}}
    <div class="mb-4 rounded-kr-md bg-kr-ink px-4 py-4 text-white shadow-kr-card">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-white/70">Beban kerja pada periode ini</p>
        <p class="mt-1 text-2xl font-bold tracking-tight">{{ $totalBox }} box</p>
        <p class="mt-1 text-xs text-white/70">{{ $jumlahPesanan }} pesanan aktif &middot; {{ $totalSnack }} snack</p>

        @if ($jumlahBatal > 0)
            <p class="mt-1 text-xs text-white/60">{{ $jumlahBatal }} pesanan batal tidak dihitung.</p>
        @endif
    </div>

    {{-- Daftar per tanggal acara --}}
    <div class="space-y-4">
    @forelse ($grup as $kunciTanggal => $daftar)
        <section class="overflow-hidden rounded-kr-md border border-kr-line bg-white shadow-kr-card">
            <div class="flex items-center justify-between gap-3 border-b border-kr-line/70 bg-kr-tint px-4 py-3">
                <h2 class="text-sm font-bold text-kr-ink">
                    {{ $labelTanggal((string) $kunciTanggal) }}
                </h2>
                <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-[11px] font-bold text-kr-muted ring-1 ring-kr-line">
                    {{ $daftar->count() }} pesanan
                </span>
            </div>

            <div class="kr-table-wrap">
                <table class="kr-table">
                    <thead>
                        <tr>
                            <th scope="col">Pelanggan &amp; nomor</th>
                            <th scope="col">Porsi</th>
                            <th scope="col">Status</th>
                            <th scope="col">Pembayaran</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($daftar as $item)
                            @php
                                $batal = $item->status === 'batal';
                                $box = $item->jumlah_box;
                                $snack = $item->jumlah_snack;
                            @endphp

                            <tr class="{{ $batal ? 'is-cancelled' : '' }}">
                                <td data-label="Pelanggan" class="cell-title">
                                    <a href="{{ route('pesanan.show', $item) }}" class="block">
                                        <span class="block truncate text-sm font-bold {{ $batal ? 'text-kr-muted line-through' : 'text-kr-ink' }}">
                                            {{ $item->pelanggan?->nama ?? 'Pelanggan' }}
                                        </span>
                                        <span class="mt-0.5 block text-[11px] font-semibold uppercase tracking-wide text-kr-faint">
                                            {{ $item->nomor }} &middot; {{ $item->jalur_pesanan === 'whatsapp' ? 'WhatsApp' : 'Langsung' }}
                                        </span>
                                    </a>
                                </td>

                                <td data-label="Porsi" class="whitespace-nowrap">
                                    <span class="strong">{{ $box }} box</span>
                                    @if ($snack > 0)
                                        <span class="muted">&middot; {{ $snack }} snack</span>
                                    @endif
                                </td>

                                <td data-label="Status">
                                    <div class="flex flex-wrap items-center gap-1.5 lg:justify-start">
                                        <x-status-pesanan :status="$item->status" />
                                        @if ($batal)
                                            <span class="rounded-full bg-kr-soft px-2 py-0.5 text-[11px] font-bold text-kr-muted">
                                                Tidak dihitung
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td data-label="Pembayaran">
                                    <x-status-bayar :status="$item->status_bayar" />
                                </td>

                                <td class="cell-actions" data-label="">
                                    <a href="{{ route('pesanan.show', $item) }}"
                                       class="inline-flex min-h-9 items-center gap-1 rounded-kr-sm border border-kr-line bg-white px-2.5 py-1.5 text-xs font-bold text-kr-muted transition hover:border-kr-line-strong hover:text-kr-primary">
                                        Detail
                                        <span aria-hidden="true">&rarr;</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @empty
        <x-empty-state judul="Belum ada jadwal"
                       deskripsi="Tidak ada pesanan pada periode ini. Coba rentang lain atau tambah pesanan baru.">
            <a href="{{ route('pesanan.create') }}"
               class="inline-flex min-h-12 items-center gap-1.5 rounded-kr-sm bg-kr-primary px-4 py-3 text-base font-bold text-white shadow-kr-raised transition hover:bg-kr-accent">
                Buat Pesanan Baru
            </a>
        </x-empty-state>
    @endforelse
    </div>
@endsection
