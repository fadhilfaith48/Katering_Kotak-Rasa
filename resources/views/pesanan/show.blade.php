@extends('layouts.app')

@section('title', $pesanan->nomor)

@section('content')
    @php
        $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
        $bisaHapus = auth()->user()->isPemilik() && ! $pesanan->sudahAdaPembayaran();
    @endphp

    <x-page-header :judul="$pesanan->nomor" deskripsi="Detail pesanan dan rincian item.">
        <x-slot:aksi>
            <a href="{{ route('pesanan.edit', $pesanan) }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                </svg>
                Ubah
            </a>
        </x-slot:aksi>
    </x-page-header>

    {{-- Ringkasan --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <p class="truncate text-base font-bold text-slate-900">{{ $pesanan->pelanggan?->nama ?? 'Pelanggan' }}</p>
                @if ($pesanan->pelanggan?->no_telepon_wa)
                    <p class="mt-0.5 text-xs text-slate-500">{{ $pesanan->pelanggan->no_telepon_wa }}</p>
                @endif
            </div>

            <div class="flex shrink-0 flex-col items-end gap-1">
                <x-status-pesanan :status="$pesanan->status" />
                <x-status-bayar :status="$pesanan->status_bayar" />
            </div>
        </div>

        <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-3 text-xs">
            <div>
                <dt class="font-semibold uppercase tracking-wide text-slate-400">Tanggal pesan</dt>
                <dd class="mt-0.5 font-semibold text-slate-700">
                    {{ $pesanan->tanggal_pesan?->locale('id')->isoFormat('D MMM YYYY') }}
                </dd>
            </div>

            <div>
                <dt class="font-semibold uppercase tracking-wide text-slate-400">Tanggal acara</dt>
                <dd class="mt-0.5 font-semibold text-slate-700">
                    {{ $pesanan->tanggal_acara?->locale('id')->isoFormat('dddd, D MMM YYYY') }}
                </dd>
            </div>

            <div>
                <dt class="font-semibold uppercase tracking-wide text-slate-400">Jalur pesanan</dt>
                <dd class="mt-0.5 font-semibold text-slate-700">
                    {{ $pesanan->jalur_pesanan === 'whatsapp' ? 'WhatsApp' : 'Langsung' }}
                </dd>
            </div>

            <div>
                <dt class="font-semibold uppercase tracking-wide text-slate-400">Jumlah box</dt>
                <dd class="mt-0.5 font-semibold text-slate-700">{{ $pesanan->jumlah_box }} box</dd>
            </div>
        </dl>

        @if ($pesanan->catatan_khusus)
            <div class="mt-3 rounded-xl bg-amber-50 px-3 py-2.5 text-xs text-amber-800">
                <span class="font-bold uppercase tracking-wide">Catatan:</span>
                {{ $pesanan->catatan_khusus }}
            </div>
        @endif
    </section>

    {{-- Item --}}
    <section class="mt-4 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
            <h2 class="text-sm font-bold text-slate-900">Rincian Item</h2>
        </div>

        @forelse ($pesanan->items as $item)
            <div class="flex items-start justify-between gap-3 border-b border-slate-100 px-4 py-3 last:border-b-0">
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-slate-800">{{ $item->menu?->nama_menu ?? 'Menu' }}</p>
                    <p class="mt-0.5 text-[11px] text-slate-400">
                        {{ $item->jumlah }} &times; {{ $rupiah($item->harga_satuan) }}
                    </p>
                </div>

                <span class="shrink-0 text-sm font-semibold text-slate-800">{{ $rupiah($item->subtotal) }}</span>
            </div>
        @empty
            <p class="px-4 py-6 text-center text-sm text-slate-400">Belum ada item pada pesanan ini.</p>
        @endforelse

        <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-4 py-3">
            <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Total pesanan</span>
            <span class="text-lg font-bold text-slate-900">{{ $rupiah($pesanan->total) }}</span>
        </div>
    </section>

    {{-- Ringkasan pembayaran --}}
    <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <h2 class="text-sm font-bold text-slate-900">Pembayaran</h2>

        <dl class="mt-3 space-y-2 text-sm">
            <div class="flex items-center justify-between">
                <dt class="text-slate-500">Sudah dibayar</dt>
                <dd class="font-semibold text-slate-800">{{ $rupiah($pesanan->total_dibayar) }}</dd>
            </div>

            @if ((float) $pesanan->dp_hangus > 0)
                <div class="flex items-center justify-between">
                    <dt class="text-rose-500">DP hangus</dt>
                    <dd class="font-semibold text-rose-600">{{ $rupiah($pesanan->dp_hangus) }}</dd>
                </div>
            @endif

            <div class="flex items-center justify-between border-t border-slate-100 pt-2">
                <dt class="font-semibold text-slate-600">Sisa tagihan</dt>
                <dd class="text-base font-bold {{ $pesanan->sisa_tagihan > 0 ? 'text-orange-600' : 'text-emerald-600' }}">
                    {{ $rupiah($pesanan->sisa_tagihan) }}
                </dd>
            </div>
        </dl>

        <p class="mt-3 rounded-xl bg-slate-50 px-3 py-2 text-[11px] text-slate-400">
            Pencatatan pembayaran (DP/pelunasan) tersedia pada tahap F2.
        </p>
    </section>

    {{-- Aksi berisiko --}}
    @if (auth()->user()->isPemilik())
        <section class="mt-4 rounded-2xl border border-rose-200 bg-rose-50/60 p-4">
            <h2 class="text-sm font-bold text-rose-800">Zona berisiko</h2>

            @if ($bisaHapus)
                <p class="mt-1 text-xs text-rose-700">
                    Pesanan ini belum memiliki pembayaran sehingga masih bisa dihapus permanen.
                </p>

                <form method="POST"
                      action="{{ route('pesanan.destroy', $pesanan) }}"
                      class="mt-3"
                      onsubmit="return confirm('Hapus pesanan {{ $pesanan->nomor }} secara permanen? Tindakan ini tidak bisa dibatalkan.');">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-rose-300 bg-white px-3.5 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100">
                        Hapus Pesanan
                    </button>
                </form>
            @else
                <p class="mt-1 text-xs text-rose-700">
                    Pesanan sudah memiliki pembayaran sehingga tidak bisa dihapus.
                    Gunakan status <b>Batal</b> melalui tombol Ubah (DP hangus bila pembatalan H-1).
                </p>
            @endif
        </section>
    @endif

    <div class="mt-4">
        <a href="{{ route('pesanan.index') }}"
           class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-700">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke daftar pesanan
        </a>
    </div>
@endsection
