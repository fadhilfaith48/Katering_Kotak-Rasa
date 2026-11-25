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
                   class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
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
                                class="inline-flex items-center gap-1.5 rounded-xl border border-rose-300 bg-white px-3 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">
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

            <div>
                <dt class="font-semibold uppercase tracking-wide text-slate-400">Jumlah snack</dt>
                <dd class="mt-0.5 font-semibold text-slate-700">{{ $pesanan->jumlah_snack }} snack</dd>
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

    {{-- Pembayaran --}}
    <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-2">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Pembayaran</h2>
                <p class="mt-0.5 text-[11px] text-slate-400">DP dan pelunasan tercatat di sini.</p>
            </div>

            <x-status-bayar :status="$pesanan->status_bayar" class="shrink-0" />
        </div>

        @if ($errors->any())
            <div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-xs text-rose-800">
                <p class="font-bold">Periksa kembali isian pembayaran:</p>
                <ul class="mt-1 list-inside list-disc space-y-0.5">
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <dl class="mt-3 space-y-2 border-y border-slate-100 py-3 text-sm">
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

            <div class="flex items-center justify-between">
                <dt class="font-semibold text-slate-600">Sisa tagihan</dt>
                <dd class="text-base font-bold {{ $pesanan->sisa_tagihan > 0 ? 'text-orange-600' : 'text-emerald-600' }}">
                    {{ $rupiah($pesanan->sisa_tagihan) }}
                </dd>
            </div>
        </dl>

        @if (! $bisaBayar)
            <p class="mt-3 rounded-xl bg-rose-50 px-3 py-2 text-[11px] text-rose-700">
                Pesanan sudah dibatalkan. Sisa tagihan ditutup dan pembayaran tidak bisa ditambahkan.
                @if ((float) $pesanan->dp_hangus > 0)
                    DP hangus tetap dihitung sebagai pemasukan pada laporan.
                @endif
            </p>
        @endif

        {{-- Riwayat pembayaran --}}
        @if ($pesanan->pembayaran->isNotEmpty())
            <div class="mt-4">
                <h3 class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Riwayat Pembayaran</h3>

                <div class="mt-2 space-y-2">
                    @foreach ($pesanan->pembayaran->sortByDesc('tanggal_bayar') as $bayar)
                        @php
                            $hangus = $bayar->jenis === 'dp_hangus';
                        @endphp

                        <div class="rounded-xl border p-3 {{ $hangus ? 'border-rose-200 bg-rose-50/70' : 'border-slate-200 bg-slate-50/60' }}">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ring-1 {{ $hangus ? 'bg-rose-100 text-rose-700 ring-rose-200' : ($bayar->jenis === 'dp' ? 'bg-amber-50 text-amber-700 ring-amber-200' : 'bg-emerald-50 text-emerald-700 ring-emerald-200') }}">
                                            {{ $bayar->label_jenis }}
                                        </span>
                                        <span class="text-[11px] text-slate-500">
                                            {{ $bayar->tanggal_bayar?->locale('id')->isoFormat('D MMM YYYY') }}
                                        </span>
                                    </div>

                                    <p class="mt-1.5 text-base font-bold {{ $hangus ? 'text-rose-700' : 'text-slate-900' }}">
                                        {{ $rupiah($bayar->nominal) }}
                                    </p>

                                    @if ($bayar->catatan)
                                        <p class="mt-0.5 break-words text-[11px] text-slate-500">{{ $bayar->catatan }}</p>
                                    @endif

                                    @if ($hangus)
                                        <p class="mt-1 text-[11px] font-semibold text-rose-600">
                                            Otomatis saat pembatalan H-1 &middot; tetap masuk pemasukan
                                        </p>
                                    @endif
                                </div>

                                @if ($pemilik)
                                    @unless ($hangus)
                                        <button type="button"
                                                data-bayar-toggle="{{ $bayar->id }}"
                                                class="shrink-0 rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-600 transition hover:bg-slate-100">
                                            Ubah
                                        </button>
                                    @endunless
                                @endif
                            </div>

                            @if ($pemilik)
                                @if ($hangus)
                                    <p class="mt-2 border-t border-rose-200 pt-2 text-[11px] text-rose-600">
                                        DP hangus dibuat otomatis oleh sistem sehingga tidak dapat diubah atau dihapus.
                                    </p>
                                @else
                                    <div id="form-bayar-{{ $bayar->id }}" class="mt-2 hidden border-t border-slate-200 pt-2">
                                        <form method="POST" action="{{ route('pembayaran.update', $bayar) }}" class="space-y-2">
                                            @csrf
                                            @method('PUT')

                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-slate-400" for="tanggal-{{ $bayar->id }}">Tanggal</label>
                                                    <input id="tanggal-{{ $bayar->id }}"
                                                           type="date"
                                                           name="tanggal_bayar"
                                                           value="{{ old('tanggal_bayar', $bayar->tanggal_bayar?->toDateString()) }}"
                                                           max="{{ today()->toDateString() }}"
                                                           required
                                                           class="w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/25">
                                                </div>

                                                <div>
                                                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-slate-400" for="nominal-{{ $bayar->id }}">Nominal</label>
                                                    <input id="nominal-{{ $bayar->id }}"
                                                           type="number"
                                                           name="nominal"
                                                           min="1"
                                                           step="any"
                                                           inputmode="decimal"
                                                           value="{{ old('nominal', $bayar->nominal) }}"
                                                           required
                                                           class="w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/25">
                                                </div>
                                            </div>

                                            <div>
                                                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-slate-400" for="jenis-{{ $bayar->id }}">Jenis</label>
                                                <select id="jenis-{{ $bayar->id }}"
                                                        name="jenis"
                                                        class="w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/25">
                                                    @foreach (['dp' => 'DP', 'pelunasan' => 'Pelunasan'] as $nilai => $label)
                                                        <option value="{{ $nilai }}" @selected(old('jenis', $bayar->jenis) === $nilai)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-slate-400" for="catatan-{{ $bayar->id }}">Catatan</label>
                                                <input id="catatan-{{ $bayar->id }}"
                                                       type="text"
                                                       name="catatan"
                                                       maxlength="500"
                                                       value="{{ old('catatan', $bayar->catatan) }}"
                                                       placeholder="Opsional"
                                                       class="w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/25">
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <button type="submit"
                                                        class="rounded-lg bg-orange-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-orange-700">
                                                    Simpan
                                                </button>

                                                <button type="button"
                                                        data-bayar-toggle="{{ $bayar->id }}"
                                                        class="rounded-lg px-2 py-1.5 text-xs font-semibold text-slate-500 transition hover:bg-slate-100">
                                                    Tutup
                                                </button>
                                            </div>
                                        </form>

                                        <form method="POST"
                                              action="{{ route('pembayaran.destroy', $bayar) }}"
                                              class="mt-2"
                                              onsubmit="return confirm('Hapus pembayaran {{ $rupiah($bayar->nominal) }} dari pesanan {{ $pesanan->nomor }}?');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="rounded-lg px-2 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50">
                                                Hapus pembayaran
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <p class="mt-3 rounded-xl border border-dashed border-slate-200 px-3 py-4 text-center text-xs text-slate-400">
                Belum ada pembayaran yang dicatat.
            </p>
        @endif

        {{-- Form tambah pembayaran --}}
        @if ($bisaBayar)
            <form method="POST"
                  action="{{ route('pembayaran.store', $pesanan) }}"
                  class="mt-4 space-y-3 border-t border-slate-100 pt-4">
                @csrf

                <h3 class="text-xs font-bold text-slate-800">Tambah Pembayaran</h3>
                <p class="text-[11px] text-slate-400">
                    Sisa tagihan saat ini <b class="text-slate-600">{{ $rupiah($pesanan->sisa_tagihan) }}</b>.
                    Nominal tidak boleh melebihi sisa tagihan.
                </p>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label for="tanggal_bayar" class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-slate-400">Tanggal bayar</label>
                        <input id="tanggal_bayar"
                               name="tanggal_bayar"
                               type="date"
                               value="{{ old('tanggal_bayar', today()->toDateString()) }}"
                               max="{{ today()->toDateString() }}"
                               required
                               class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-500/25 @error('tanggal_bayar') border-rose-400 @enderror">
                    </div>

                    <div>
                        <label for="nominal" class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-slate-400">Nominal</label>
                        <input id="nominal"
                               name="nominal"
                               type="number"
                               min="1"
                               step="any"
                               inputmode="decimal"
                               value="{{ old('nominal') }}"
                               placeholder="0"
                               required
                               class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-500/25 @error('nominal') border-rose-400 @enderror">
                    </div>
                </div>

                <div>
                    <span class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-slate-400">Jenis pembayaran</span>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach (['dp' => 'DP', 'pelunasan' => 'Pelunasan'] as $nilai => $label)
                            <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 transition has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 has-[:checked]:text-orange-700">
                                <input type="radio"
                                       name="jenis"
                                       value="{{ $nilai }}"
                                       @checked(old('jenis', 'dp') === $nilai)
                                       class="h-4 w-4 accent-orange-600">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label for="catatan" class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-slate-400">Catatan <span class="font-normal normal-case text-slate-400">(opsional)</span></label>
                    <input id="catatan"
                           name="catatan"
                           type="text"
                           maxlength="500"
                           value="{{ old('catatan') }}"
                           placeholder="Contoh: transfer BCA"
                           class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-500/25">
                </div>

                <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm shadow-orange-600/25 transition hover:bg-orange-700 active:scale-[0.99]">
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
           class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-700">
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
