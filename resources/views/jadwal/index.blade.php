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
          class="mb-4 rounded-2xl border border-kr-line bg-white p-3 shadow-kr-card">
        <label for="tanggal" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-kr-muted">
            Atau pilih tanggal tertentu
        </label>

        <div class="flex items-end gap-2">
            <input id="tanggal"
                   type="date"
                   name="tanggal"
                   value="{{ $rentang === 'tanggal' ? $tanggal : '' }}"
                   class="min-w-0 flex-1 rounded-xl border border-kr-line-strong bg-white px-3 py-3 text-base text-kr-ink outline-none transition focus:border-kr-primary focus:ring-2 focus:ring-kr-primary/25">

            <button type="submit"
                    class="shrink-0 min-h-12 rounded-[14px] bg-kr-primary px-4 py-3 text-base font-bold text-white transition hover:bg-kr-accent">
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
    <div class="mb-4 rounded-2xl bg-kr-ink px-4 py-4 text-white shadow-kr-card">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-white/70">Beban kerja pada periode ini</p>
        <p class="mt-1 text-2xl font-bold tracking-tight">{{ $totalBox }} box</p>
        <p class="mt-1 text-xs text-white/70">{{ $jumlahPesanan }} pesanan aktif &middot; {{ $totalSnack }} snack</p>

        @if ($jumlahBatal > 0)
            <p class="mt-1 text-xs text-white/60">{{ $jumlahBatal }} pesanan batal tidak dihitung.</p>
        @endif
    </div>

    {{-- Daftar per tanggal acara --}}
    <div class="lg:grid lg:grid-cols-2 lg:items-start lg:gap-5">
    @forelse ($grup as $kunciTanggal => $daftar)
        <section class="mb-5 lg:mb-0">
            <div class="mb-2 flex items-center gap-2">
                <h2 class="text-xs font-bold uppercase tracking-wide text-kr-muted">
                    {{ $labelTanggal((string) $kunciTanggal) }}
                </h2>
                <span class="rounded-full bg-kr-soft px-2 py-0.5 text-[10px] font-bold text-kr-muted">
                    {{ $daftar->count() }} pesanan
                </span>
            </div>

            @foreach ($daftar as $item)
                @php
                    $batal = $item->status === 'batal';
                    $box = $item->jumlah_box;
                    $snack = $item->jumlah_snack;
                @endphp

                <a href="{{ route('pesanan.show', $item) }}"
                   class="mb-2 block rounded-2xl border p-4 shadow-kr-card transition hover:border-kr-line-strong hover:shadow-md active:scale-[0.995] {{ $batal ? 'border-kr-line bg-kr-tint opacity-70' : 'border-kr-line bg-white' }}">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-kr-faint">{{ $item->nomor }}</p>
                            <p class="mt-0.5 truncate text-sm font-bold {{ $batal ? 'text-kr-muted line-through' : 'text-kr-ink' }}">
                                {{ $item->pelanggan?->nama ?? 'Pelanggan' }}
                            </p>
                        </div>

                        <div class="flex shrink-0 items-center gap-1.5">
                            @if ($batal)
                                <span class="rounded-full bg-kr-soft px-2 py-0.5 text-[10px] font-bold text-kr-muted">
                                    Tidak dihitung
                                </span>
                            @endif
                            <x-status-pesanan :status="$item->status" />
                        </div>
                    </div>

                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-[11px] text-kr-muted">
                        <span class="font-semibold {{ $batal ? 'text-kr-faint' : 'text-kr-primary' }}">
                            {{ $box }} box
                        </span>
                        @if ($snack > 0)
                            <span>{{ $snack }} snack</span>
                        @endif
                        <span>{{ $item->jalur_pesanan === 'whatsapp' ? 'WhatsApp' : 'Langsung' }}</span>
                    </div>

                    <div class="mt-3 flex items-center justify-between border-t border-kr-line/70 pt-2.5">
                        <x-status-bayar :status="$item->status_bayar" />

                        <span class="text-[11px] font-bold text-kr-primary">Detail &rarr;</span>
                    </div>
                </a>
            @endforeach
        </section>
    @empty
        <div class="lg:col-span-2">
            <x-empty-state judul="Belum ada jadwal"
                           deskripsi="Tidak ada pesanan pada periode ini. Coba rentang lain atau tambah pesanan baru.">
                <a href="{{ route('pesanan.create') }}"
                   class="inline-flex min-h-12 items-center gap-1.5 rounded-[14px] bg-kr-primary px-4 py-3 text-base font-bold text-white shadow-kr-primary/25 transition hover:bg-kr-accent">
                    Buat Pesanan Baru
                </a>
            </x-empty-state>
        </div>
    @endforelse
    </div>
@endsection
