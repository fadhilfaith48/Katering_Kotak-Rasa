@extends('layouts.app')

@section('title', 'Pesanan')

@section('content')
    <x-page-header judul="Pesanan" deskripsi="Daftar pesanan katering beserta status pengerjaannya.">
        <x-slot:aksi>
            <a href="{{ route('pesanan.create') }}"
               class="inline-flex min-h-12 items-center gap-1.5 rounded-kr-sm bg-kr-primary px-4 py-3 text-base font-bold text-white shadow-kr-raised transition hover:bg-kr-accent active:scale-[0.99]">
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
    <form method="GET" action="{{ route('pesanan.index') }}" class="mb-3 lg:max-w-xl">
        @if ($status !== '')
            <input type="hidden" name="status" value="{{ $status }}">
        @endif

        <div class="flex items-center gap-2">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-9 place-items-center text-kr-faint">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </span>
                <input type="search"
                       name="q"
                       value="{{ $cari }}"
                       placeholder="Cari nama pelanggan atau nomor"
                       class="w-full rounded-kr-sm border border-kr-line-strong bg-white py-3 pl-9 pr-3 text-base text-kr-ink outline-none transition placeholder:text-kr-faint focus:border-kr-primary focus:ring-2 focus:ring-kr-primary/25">
            </div>

            <button type="submit"
                    class="rounded-kr-sm border border-kr-line-strong bg-white px-3.5 py-2.5 text-sm font-semibold text-kr-muted transition hover:bg-kr-soft">
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
               class="shrink-0 rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $aktif ? 'border-kr-primary bg-kr-primary text-white' : 'border-kr-line bg-white text-kr-muted hover:border-kr-line-strong hover:text-kr-primary' }}"
               @if ($aktif) aria-current="page" @endif>
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Daftar --}}
    <section class="overflow-hidden rounded-kr-md border border-kr-line bg-white shadow-kr-card">
        <div class="flex items-start justify-between gap-3 border-b border-kr-line/70 px-4 py-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wide text-kr-faint">Daftar pesanan</p>
                <h2 class="mt-0.5 text-base font-bold text-kr-ink">
                    {{ $pesanan->total() }} pesanan
                    @if ($status !== '')
                        &middot; {{ $daftarStatus[$status] ?? $status }}
                    @endif
                </h2>
            </div>

            <a href="{{ route('pesanan.index') }}"
               class="shrink-0 pt-0.5 text-xs font-semibold text-kr-muted transition hover:text-kr-primary">
                Atur ulang
            </a>
        </div>

        @if ($pesanan->isEmpty())
            <div class="p-4">
                <x-empty-state judul="Belum ada pesanan" deskripsi="Pesanan yang kamu catat akan muncul di sini.">
                    <a href="{{ route('pesanan.create') }}"
                       class="inline-flex min-h-12 items-center gap-1.5 rounded-kr-sm bg-kr-primary px-4 py-3 text-base font-bold text-white shadow-kr-raised transition hover:bg-kr-accent">
                        Buat Pesanan Pertama
                    </a>
                </x-empty-state>
            </div>
        @else
            <div class="kr-table-wrap">
                <table class="kr-table">
                    <thead>
                        <tr>
                            <th scope="col">Pelanggan &amp; nomor</th>
                            <th scope="col">Tanggal acara</th>
                            <th scope="col">Porsi</th>
                            <th scope="col">Status pesanan</th>
                            <th scope="col">Pembayaran</th>
                            <th scope="col" class="num">Total</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pesanan as $item)
                            <tr class="{{ $item->status === 'batal' ? 'is-cancelled' : '' }}">
                                <td data-label="Pelanggan" class="cell-title">
                                    <a href="{{ route('pesanan.show', $item) }}" class="block">
                                        <span class="block truncate text-sm font-bold text-kr-ink">
                                            {{ $item->pelanggan?->nama ?? 'Pelanggan' }}
                                        </span>
                                        <span class="mt-0.5 block text-[11px] font-semibold uppercase tracking-wide text-kr-faint">
                                            {{ $item->nomor }} &middot; {{ $item->jalur_pesanan === 'whatsapp' ? 'WhatsApp' : 'Langsung' }}
                                        </span>
                                    </a>
                                </td>

                                <td data-label="Tanggal acara" class="whitespace-nowrap">
                                    {{ $item->tanggal_acara?->locale('id')->isoFormat('ddd, D MMM YYYY') }}
                                </td>

                                <td data-label="Porsi" class="whitespace-nowrap">
                                    <span class="strong">{{ $item->jumlah_box }} box</span>
                                    @if ($item->jumlah_snack > 0)
                                        <span class="muted">&middot; {{ $item->jumlah_snack }} snack</span>
                                    @endif
                                </td>

                                <td data-label="Status pesanan">
                                    <x-status-pesanan :status="$item->status" />
                                </td>

                                <td data-label="Pembayaran">
                                    <x-status-bayar :status="$item->status_bayar" />
                                </td>

                                <td data-label="Total" class="num strong">{{ $rupiah($item->total) }}</td>

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
        @endif
    </section>

    @if ($pesanan->hasPages())
        <div class="mt-4">
            {{ $pesanan->links() }}
        </div>
    @endif
@endsection
