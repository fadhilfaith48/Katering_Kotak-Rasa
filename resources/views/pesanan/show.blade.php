@extends('layouts.app')

@section('title', $pesanan->nomor)

@section('content')
    @php
        $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
        $pemilik = auth()->user()->isPemilik();
        $bisaHapus = $pemilik && ! $pesanan->sudahAdaPembayaran();
        $bisaBayar = $pesanan->status !== 'batal';

        $konfirmasiBatal = $dpAkanHangus > 0
            ? 'Pesanan ini dibatalkan pada H-1. DP sebesar '.$rupiah($dpAkanHangus).' akan ditandai hangus dan tetap dihitung sebagai pemasukan. Lanjutkan?'
            : 'Batalkan pesanan '.$pesanan->nomor.'? Tindakan ini tidak bisa dibatalkan.';
    @endphp

    <x-page-header :judul="$pesanan->nomor" deskripsi="Detail pesanan, rincian item, dan pembayaran.">
        <x-slot:aksi>
            <div class="flex items-center gap-2">
                <a href="{{ route('pesanan.edit', $pesanan) }}"
                   class="inline-flex items-center gap-1.5 rounded-kr-sm border border-kr-line-strong bg-white px-3.5 py-2.5 text-sm font-semibold text-kr-muted transition hover:bg-kr-soft">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                    </svg>
                    Ubah
                </a>

                @if ($pemilik && $bisaBayar)
                    <form method="POST"
                          action="{{ route('pesanan.batal', $pesanan) }}"
                          onsubmit='return confirm(@json($konfirmasiBatal));'>
                        @csrf

                        <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-kr-sm border border-kr-danger/30 bg-white px-3 py-2.5 text-sm font-semibold text-kr-danger transition hover:bg-kr-danger-soft">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            Batalkan
                        </button>
                    </form>
                @endif
            </div>
        </x-slot:aksi>
    </x-page-header>

    {{-- Ringkasan --}}
    <section class="rounded-kr-md border border-kr-line bg-white p-4 shadow-kr-card">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <p class="truncate text-base font-bold text-kr-ink">{{ $pesanan->pelanggan?->nama ?? 'Pelanggan' }}</p>
                @if ($pesanan->pelanggan?->no_telepon_wa)
                    <p class="mt-0.5 text-xs text-kr-muted">{{ $pesanan->pelanggan->no_telepon_wa }}</p>
                @endif
            </div>

            <div class="flex shrink-0 flex-col items-end gap-1">
                <x-status-pesanan :status="$pesanan->status" />
                <x-status-bayar :status="$pesanan->status_bayar" />
            </div>
        </div>

        <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-kr-line/70 pt-3 text-xs lg:grid-cols-5">
            <div>
                <dt class="font-semibold uppercase tracking-wide text-kr-faint">Tanggal pesan</dt>
                <dd class="mt-0.5 font-semibold text-kr-brown">
                    {{ $pesanan->tanggal_pesan?->locale('id')->isoFormat('D MMM YYYY') }}
                </dd>
            </div>

            <div>
                <dt class="font-semibold uppercase tracking-wide text-kr-faint">Tanggal acara</dt>
                <dd class="mt-0.5 font-semibold text-kr-brown">
                    {{ $pesanan->tanggal_acara?->locale('id')->isoFormat('ddd, D MMM YYYY') }}
                </dd>
            </div>

            <div>
                <dt class="font-semibold uppercase tracking-wide text-kr-faint">Jalur pesanan</dt>
                <dd class="mt-0.5 font-semibold text-kr-brown">
                    {{ $pesanan->jalur_pesanan === 'whatsapp' ? 'WhatsApp' : 'Langsung' }}
                </dd>
            </div>

            <div>
                <dt class="font-semibold uppercase tracking-wide text-kr-faint">Jumlah box</dt>
                <dd class="mt-0.5 font-semibold text-kr-brown">{{ $pesanan->jumlah_box }} box</dd>
            </div>

            <div>
                <dt class="font-semibold uppercase tracking-wide text-kr-faint">Jumlah snack</dt>
                <dd class="mt-0.5 font-semibold text-kr-brown">{{ $pesanan->jumlah_snack }} snack</dd>
            </div>
        </dl>

        @if ($pesanan->catatan_khusus)
            <div class="mt-3 rounded-kr-sm bg-kr-warning-soft px-3 py-2.5 text-xs text-kr-warning">
                <span class="font-bold uppercase tracking-wide">Catatan:</span>
                {{ $pesanan->catatan_khusus }}
            </div>
        @endif
    </section>

    {{-- Item --}}
    <section class="mt-4 overflow-hidden rounded-kr-md border border-kr-line bg-white shadow-kr-card">
        <div class="border-b border-kr-line/70 px-4 py-3">
            <p class="text-[11px] font-bold uppercase tracking-wide text-kr-faint">Komponen paket</p>
            <h2 class="mt-0.5 text-sm font-bold text-kr-ink">Rincian Item</h2>
        </div>

        @if ($pesanan->items->isEmpty())
            <p class="px-4 py-6 text-center text-sm text-kr-faint">Belum ada item pada pesanan ini.</p>
        @else
            <div class="kr-table-wrap">
                <table class="kr-table kr-table--fixed">
                    <colgroup>
                        <col>
                        <col class="w-28">
                        <col class="w-32">
                        <col class="w-32">
                    </colgroup>
                    <thead>
                        <tr>
                            <th scope="col">Nama menu</th>
                            <th scope="col" class="num">Kuantitas</th>
                            <th scope="col" class="num">Harga satuan</th>
                            <th scope="col" class="num">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pesanan->items as $item)
                            <tr>
                                <td data-label="Menu" class="cell-title">
                                    <span class="text-sm font-semibold text-kr-ink">{{ $item->menu?->nama_menu ?? 'Menu' }}</span>
                                </td>
                                <td data-label="Kuantitas" class="num">{{ $item->jumlah }} porsi</td>
                                <td data-label="Harga satuan" class="num">{{ $rupiah($item->harga_satuan) }}</td>
                                <td data-label="Subtotal" class="num strong">{{ $rupiah($item->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="flex items-center justify-between border-t border-kr-line bg-kr-tint px-4 py-3">
            <span class="text-xs font-bold uppercase tracking-wide text-kr-muted">Total pesanan</span>
            <span class="text-lg font-bold text-kr-ink tabular-nums">{{ $rupiah($pesanan->total) }}</span>
        </div>
    </section>

    {{-- Pembayaran --}}
    <section class="mt-4 rounded-kr-md border border-kr-line bg-white p-4 shadow-kr-card">
        <div class="flex items-start justify-between gap-2">
            <div>
                <h2 class="text-sm font-bold text-kr-ink">Pembayaran</h2>
                <p class="mt-0.5 text-[11px] text-kr-faint">DP dan pelunasan tercatat di sini.</p>
            </div>

            <x-status-bayar :status="$pesanan->status_bayar" class="shrink-0" />
        </div>

        @if ($errors->any())
            <div class="mt-3 rounded-kr-sm border border-kr-danger/20 bg-kr-danger-soft px-3 py-2.5 text-xs text-kr-danger">
                <p class="font-bold">Periksa kembali isian pembayaran:</p>
                <ul class="mt-1 list-inside list-disc space-y-0.5">
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <dl class="mt-3 space-y-2 border-y border-kr-line/70 py-3 text-sm">
            <div class="flex items-center justify-between">
                <dt class="text-kr-muted">Sudah dibayar</dt>
                <dd class="font-semibold text-kr-ink tabular-nums">{{ $rupiah($pesanan->total_dibayar) }}</dd>
            </div>

            @if ((float) $pesanan->dp_hangus > 0)
                <div class="flex items-center justify-between">
                    <dt class="text-kr-danger">DP hangus</dt>
                    <dd class="font-semibold text-kr-danger tabular-nums">{{ $rupiah($pesanan->dp_hangus) }}</dd>
                </div>
            @endif

            <div class="flex items-center justify-between">
                <dt class="font-semibold text-kr-muted">Sisa tagihan</dt>
                <dd class="text-base font-bold tabular-nums {{ $pesanan->sisa_tagihan > 0 ? 'text-kr-primary' : 'text-kr-success' }}">
                    {{ $rupiah($pesanan->sisa_tagihan) }}
                </dd>
            </div>
        </dl>

        @if (! $bisaBayar)
            <p class="mt-3 rounded-kr-sm bg-kr-danger-soft px-3 py-2 text-[11px] text-kr-danger">
                Pesanan sudah dibatalkan. Sisa tagihan ditutup dan pembayaran tidak bisa ditambahkan.
                @if ((float) $pesanan->dp_hangus > 0)
                    DP hangus tetap dihitung sebagai pemasukan pada laporan.
                @endif
            </p>
        @endif

        {{-- Riwayat pembayaran --}}
        @if ($pesanan->pembayaran->isNotEmpty())
            <div class="mt-4">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-[11px] font-bold uppercase tracking-wide text-kr-faint">Riwayat Pembayaran</h3>
                    <span class="text-[11px] font-semibold text-kr-faint">{{ $pesanan->pembayaran->count() }} catatan</span>
                </div>

                <div class="kr-table-wrap mt-2">
                    <table class="kr-table kr-table--fixed">
                        <colgroup>
                            <col class="w-28">
                            <col class="w-32">
                            <col>
                            <col class="w-32">
                            <col class="w-24">
                        </colgroup>
                        <thead>
                            <tr>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Jenis</th>
                                <th scope="col">Catatan</th>
                                <th scope="col" class="num">Nominal</th>
                                <th scope="col"><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pesanan->pembayaran->sortByDesc('tanggal_bayar') as $bayar)
                                @php
                                    $hangus = $bayar->jenis === 'dp_hangus';
                                @endphp

                                <tr>
                                    <td data-label="Tanggal" class="cell-title whitespace-nowrap">
                                        {{ $bayar->tanggal_bayar?->locale('id')->isoFormat('D MMM YYYY') }}
                                    </td>

                                    <td data-label="Jenis">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 {{ $hangus ? 'bg-kr-danger-soft text-kr-danger ring-kr-danger/20' : ($bayar->jenis === 'dp' ? 'bg-kr-warning-soft text-kr-warning ring-kr-warning/20' : 'bg-kr-success-soft text-kr-success-deep ring-kr-success/20') }}">
                                            {{ $bayar->label_jenis }}
                                        </span>
                                    </td>

                                    <td data-label="Catatan">
                                        <span class="text-sm text-kr-muted">{{ $bayar->catatan ?: '—' }}</span>
                                        @if ($hangus)
                                            <span class="mt-0.5 block text-[11px] font-semibold text-kr-danger">
                                                Otomatis saat pembatalan H-1 &middot; tetap masuk pemasukan
                                            </span>
                                        @endif
                                    </td>

                                    <td data-label="Nominal" class="num strong {{ $hangus ? 'text-kr-danger' : 'text-kr-ink' }}">
                                        {{ $rupiah($bayar->nominal) }}
                                    </td>

                                    <td class="cell-actions" data-label="">
                                        @if ($pemilik)
                                            @if ($hangus)
                                                <span class="text-[11px] text-kr-faint">Terkunci</span>
                                            @else
                                                <button type="button"
                                                        data-bayar-toggle="{{ $bayar->id }}"
                                                        class="rounded-kr-sm border border-kr-line-strong bg-white px-2.5 py-1.5 text-xs font-semibold text-kr-muted transition hover:bg-kr-soft">
                                                    Ubah
                                                </button>
                                            @endif
                                        @endif
                                    </td>
                                </tr>

                                @if ($pemilik && ! $hangus)
                                    <tr id="form-bayar-{{ $bayar->id }}" class="kr-table-detail hidden">
                                        <td colspan="5">
                                            <div class="space-y-2">
                                                <form method="POST" action="{{ route('pembayaran.update', $bayar) }}" class="space-y-2">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="grid grid-cols-2 gap-2">
                                                        <div>
                                                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-kr-faint" for="tanggal-{{ $bayar->id }}">Tanggal</label>
                                                            <input id="tanggal-{{ $bayar->id }}"
                                                                   type="date"
                                                                   name="tanggal_bayar"
                                                                   value="{{ old('tanggal_bayar', $bayar->tanggal_bayar?->toDateString()) }}"
                                                                   max="{{ today()->toDateString() }}"
                                                                   required
                                                                   class="w-full rounded-kr-sm border border-kr-line-strong bg-white px-2.5 py-2 text-base outline-none focus:border-kr-primary focus:ring-2 focus:ring-kr-primary/25">
                                                        </div>

                                                        <div>
                                                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-kr-faint" for="nominal-{{ $bayar->id }}">Nominal</label>
                                                            <input id="nominal-{{ $bayar->id }}"
                                                                   type="number"
                                                                   name="nominal"
                                                                   min="1"
                                                                   step="any"
                                                                   inputmode="decimal"
                                                                   value="{{ old('nominal', $bayar->nominal) }}"
                                                                   required
                                                                   class="w-full rounded-kr-sm border border-kr-line-strong bg-white px-2.5 py-2 text-base outline-none focus:border-kr-primary focus:ring-2 focus:ring-kr-primary/25">
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-kr-faint" for="jenis-{{ $bayar->id }}">Jenis</label>
                                                        <select id="jenis-{{ $bayar->id }}"
                                                                name="jenis"
                                                                class="w-full rounded-kr-sm border border-kr-line-strong bg-white px-2.5 py-2 text-base outline-none focus:border-kr-primary focus:ring-2 focus:ring-kr-primary/25">
                                                            @foreach (['dp' => 'DP', 'pelunasan' => 'Pelunasan'] as $nilai => $label)
                                                                <option value="{{ $nilai }}" @selected(old('jenis', $bayar->jenis) === $nilai)>{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-kr-faint" for="catatan-{{ $bayar->id }}">Catatan</label>
                                                        <input id="catatan-{{ $bayar->id }}"
                                                               type="text"
                                                               name="catatan"
                                                               maxlength="500"
                                                               value="{{ old('catatan', $bayar->catatan) }}"
                                                               placeholder="Opsional"
                                                               class="w-full rounded-kr-sm border border-kr-line-strong bg-white px-2.5 py-2 text-base outline-none focus:border-kr-primary focus:ring-2 focus:ring-kr-primary/25">
                                                    </div>

                                                    <div class="flex items-center gap-2">
                                                        <button type="submit"
                                                                class="rounded-kr-sm bg-kr-primary px-3 py-1.5 text-xs font-bold text-white transition hover:bg-kr-accent">
                                                            Simpan
                                                        </button>

                                                        <button type="button"
                                                                data-bayar-toggle="{{ $bayar->id }}"
                                                                class="rounded-kr-sm px-2 py-1.5 text-xs font-semibold text-kr-muted transition hover:bg-kr-soft">
                                                            Tutup
                                                        </button>
                                                    </div>
                                                </form>

                                                <form method="POST"
                                                      action="{{ route('pembayaran.destroy', $bayar) }}"
                                                      onsubmit="return confirm('Hapus pembayaran {{ $rupiah($bayar->nominal) }} dari pesanan {{ $pesanan->nomor }}?');">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                            class="rounded-kr-sm px-2 py-1.5 text-xs font-semibold text-kr-danger transition hover:bg-kr-danger-soft">
                                                        Hapus pembayaran
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <p class="mt-3 rounded-kr-sm border border-dashed border-kr-line px-3 py-4 text-center text-xs text-kr-faint">
                Belum ada pembayaran yang dicatat.
            </p>
        @endif

        {{-- Form tambah pembayaran --}}
        @if ($bisaBayar)
            <form method="POST"
                  action="{{ route('pembayaran.store', $pesanan) }}"
                  class="mt-4 space-y-3 border-t border-kr-line/70 pt-4">
                @csrf

                <h3 class="text-xs font-bold text-kr-ink">Tambah Pembayaran</h3>
                <p class="text-[11px] text-kr-faint">
                    Sisa tagihan saat ini <b class="text-kr-muted">{{ $rupiah($pesanan->sisa_tagihan) }}</b>.
                    Nominal tidak boleh melebihi sisa tagihan.
                </p>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label for="tanggal_bayar" class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-kr-faint">Tanggal bayar</label>
                        <input id="tanggal_bayar"
                               name="tanggal_bayar"
                               type="date"
                               value="{{ old('tanggal_bayar', today()->toDateString()) }}"
                               max="{{ today()->toDateString() }}"
                               required
                               class="w-full rounded-kr-sm border border-kr-line-strong bg-white px-3 py-3 text-base outline-none transition focus:border-kr-primary focus:ring-2 focus:ring-kr-primary/25 @error('tanggal_bayar') border-rose-400 @enderror">
                    </div>

                    <div>
                        <label for="nominal" class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-kr-faint">Nominal</label>
                        <input id="nominal"
                               name="nominal"
                               type="number"
                               min="1"
                               step="any"
                               inputmode="decimal"
                               value="{{ old('nominal') }}"
                               placeholder="0"
                               required
                               class="w-full rounded-kr-sm border border-kr-line-strong bg-white px-3 py-3 text-base outline-none transition focus:border-kr-primary focus:ring-2 focus:ring-kr-primary/25 @error('nominal') border-rose-400 @enderror">
                    </div>
                </div>

                <div>
                    <span class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-kr-faint">Jenis pembayaran</span>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach (['dp' => 'DP', 'pelunasan' => 'Pelunasan'] as $nilai => $label)
                            <label class="flex cursor-pointer items-center gap-2 rounded-kr-sm border border-kr-line-strong px-3 py-2 text-sm text-kr-brown transition has-[:checked]:border-kr-primary has-[:checked]:bg-kr-tint has-[:checked]:text-kr-accent">
                                <input type="radio"
                                       name="jenis"
                                       value="{{ $nilai }}"
                                       @checked(old('jenis', 'dp') === $nilai)
                                       class="h-4 w-4 accent-kr-primary">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label for="catatan" class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-kr-faint">Catatan <span class="font-normal normal-case text-kr-faint">(opsional)</span></label>
                    <input id="catatan"
                           name="catatan"
                           type="text"
                           maxlength="500"
                           value="{{ old('catatan') }}"
                           placeholder="Contoh: transfer BCA"
                           class="w-full rounded-kr-sm border border-kr-line-strong bg-white px-3 py-3 text-base outline-none transition focus:border-kr-primary focus:ring-2 focus:ring-kr-primary/25">
                </div>

                <button type="submit"
                        class="inline-flex min-h-12 w-full items-center justify-center gap-1.5 rounded-kr-sm bg-kr-primary px-5 py-3 text-base font-bold text-white shadow-kr-raised transition hover:bg-kr-accent active:scale-[0.99]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Catat Pembayaran
                </button>
            </form>
        @endif
    </section>

    {{-- Zona berisiko --}}
    @if ($pemilik)
        <section class="mt-4 rounded-kr-md border border-kr-danger/20 bg-kr-danger-soft/60 p-4">
            <h2 class="text-sm font-bold text-kr-danger">Zona berisiko</h2>

            @if ($bisaHapus)
                <p class="mt-1 text-xs text-kr-danger">
                    Pesanan ini belum memiliki pembayaran sehingga masih bisa dihapus permanen.
                </p>

                <form method="POST"
                      action="{{ route('pesanan.destroy', $pesanan) }}"
                      class="mt-3"
                      onsubmit="return confirm('Hapus pesanan {{ $pesanan->nomor }} secara permanen? Tindakan ini tidak bisa dibatalkan.');">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-kr-sm border border-kr-danger/30 bg-white px-3.5 py-2 text-xs font-bold text-kr-danger transition hover:bg-kr-danger-soft">
                        Hapus Pesanan
                    </button>
                </form>
            @else
                <p class="mt-1 text-xs text-kr-danger">
                    @if ($bisaBayar)
                        Pesanan sudah memiliki pembayaran sehingga tidak bisa dihapus.
                        Gunakan tombol <b>Batalkan</b> di bagian atas halaman.
                        Pembatalan pada H-1 otomatis menandai DP sebagai hangus.
                    @else
                        Pesanan sudah dibatalkan sehingga tidak bisa dihapus.
                        Riwayat pembayaran tetap disimpan untuk laporan.
                    @endif
                </p>
            @endif
        </section>
    @endif

    <div class="mt-4">
        <a href="{{ route('pesanan.index') }}"
           class="inline-flex items-center gap-1.5 text-sm font-semibold text-kr-muted transition hover:text-kr-brown">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke daftar pesanan
        </a>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('[data-bayar-toggle]').forEach(function (tombol) {
                tombol.addEventListener('click', function () {
                    const form = document.getElementById('form-bayar-' + this.dataset.bayarToggle);

                    if (form) {
                        form.classList.toggle('hidden');
                    }
                });
            });
        </script>
    @endpush
@endsection
