@php
    $isEdit = isset($pesanan) && $pesanan->exists;

    $kelasInput = 'w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-500/25';
    $kelasLabel = 'mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500';

    $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');

    $pelangganTerpilih = old('pelanggan_id', $isEdit ? $pesanan->pelanggan_id : null);
    $pakaiPelangganBaru = blank($pelangganTerpilih);

    $totalAwal = collect($items)->sum(
        fn ($item) => (int) ($item['jumlah'] ?? 0) * (float) ($item['harga_satuan'] ?? 0)
    );

    $nomorBerikut = (int) collect(array_keys($items))->map(fn ($kunci) => (int) $kunci)->max() + 1;
@endphp

{{-- Ringkasan error --}}
@if ($errors->any())
    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-sm text-rose-800">
        <p class="font-semibold">Periksa kembali isian berikut:</p>
        <ul class="mt-1 list-inside list-disc space-y-0.5 text-xs">
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Pelanggan & jadwal --}}
<section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <h2 class="text-sm font-bold text-slate-900">Pelanggan &amp; Jadwal</h2>

    <div class="mt-3 space-y-3">
        <div>
            <label for="pelanggan_id" class="{{ $kelasLabel }}">Pelanggan</label>
            <select id="pelanggan_id" name="pelanggan_id" class="{{ $kelasInput }}">
                <option value="">— Pelanggan baru —</option>
                @foreach ($pelanggans as $pelanggan)
                    <option value="{{ $pelanggan->id }}" @selected((string) $pelangganTerpilih === (string) $pelanggan->id)>
                        {{ $pelanggan->nama }}{{ $pelanggan->no_telepon_wa ? ' · '.$pelanggan->no_telepon_wa : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div id="pelanggan-baru" class="space-y-3 rounded-xl border border-dashed border-orange-200 bg-orange-50/50 p-3 {{ $pakaiPelangganBaru ? '' : 'hidden' }}">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-orange-700/80">Pelanggan baru</p>

            <div>
                <label for="pelanggan_baru_nama" class="{{ $kelasLabel }}">Nama pelanggan</label>
                <input id="pelanggan_baru_nama"
                       name="pelanggan_baru_nama"
                       type="text"
                       value="{{ old('pelanggan_baru_nama') }}"
                       placeholder="Contoh: Ibu Sari"
                       class="{{ $kelasInput }}">
            </div>

            <div>
                <label for="pelanggan_baru_wa" class="{{ $kelasLabel }}">Nomor WhatsApp <span class="font-normal normal-case text-slate-400">(opsional)</span></label>
                <input id="pelanggan_baru_wa"
                       name="pelanggan_baru_wa"
                       type="tel"
                       inputmode="tel"
                       value="{{ old('pelanggan_baru_wa') }}"
                       placeholder="08xxxxxxxxxx"
                       class="{{ $kelasInput }}">
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <label for="tanggal_pesan" class="{{ $kelasLabel }}">Tanggal pesan</label>
                <input id="tanggal_pesan"
                       name="tanggal_pesan"
                       type="date"
                       value="{{ old('tanggal_pesan', $isEdit ? $pesanan->tanggal_pesan?->toDateString() : today()->toDateString()) }}"
                       class="{{ $kelasInput }}">
            </div>

            <div>
                <label for="tanggal_acara" class="{{ $kelasLabel }}">Tanggal acara <span class="text-rose-500">*</span></label>
                <input id="tanggal_acara"
                       name="tanggal_acara"
                       type="date"
                       value="{{ old('tanggal_acara', $isEdit ? $pesanan->tanggal_acara?->toDateString() : '') }}"
                       @unless ($isEdit) min="{{ today()->toDateString() }}" @endunless
                       required
                       class="{{ $kelasInput }}">
            </div>
        </div>

        <div>
            <span class="{{ $kelasLabel }}">Jalur pesanan</span>
            <div class="grid grid-cols-2 gap-2">
                @foreach (['whatsapp' => 'WhatsApp', 'langsung' => 'Langsung'] as $nilai => $label)
                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 transition has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 has-[:checked]:text-orange-700">
                        <input type="radio"
                               name="jalur_pesanan"
                               value="{{ $nilai }}"
                               @checked(old('jalur_pesanan', $isEdit ? $pesanan->jalur_pesanan : 'whatsapp') === $nilai)
                               class="h-4 w-4 accent-orange-600">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- Item pesanan --}}
<section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex items-start justify-between gap-2">
        <div>
            <h2 class="text-sm font-bold text-slate-900">Item Pesanan</h2>
            <p class="mt-0.5 text-[11px] text-slate-400">Harga otomatis mengikuti menu, masih bisa diubah.</p>
        </div>

        <button type="button"
                id="tambah-item"
                class="inline-flex shrink-0 items-center gap-1 rounded-lg border border-orange-200 bg-orange-50 px-2.5 py-1.5 text-xs font-semibold text-orange-700 transition hover:bg-orange-100">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah
        </button>
    </div>

    <div id="item-rows"
         class="mt-3 space-y-3"
         data-harga-menu='@json($menuHarga)'
         data-next-index="{{ $nomorBerikut }}">
        @foreach ($items as $indeks => $item)
            <div class="item-row rounded-xl border border-slate-200 bg-slate-50/60 p-3">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Item <span data-nomor>{{ $loop->iteration }}</span></span>
                    <button type="button"
                            data-hapus
                            class="rounded-md px-2 py-0.5 text-[11px] font-semibold text-rose-600 transition hover:bg-rose-50">
                        Hapus
                    </button>
                </div>

                <div class="mt-2">
                    <label class="{{ $kelasLabel }}">Menu</label>
                    <select name="items[{{ $indeks }}][menu_id]" data-menu class="{{ $kelasInput }}">
                        <option value="">— Pilih menu —</option>
                        @foreach ($menus as $menu)
                            <option value="{{ $menu->id }}" @selected((string) ($item['menu_id'] ?? '') === (string) $menu->id)>
                                {{ $menu->nama_menu }} · {{ $rupiah($menu->harga_standar) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-2 grid grid-cols-2 gap-2">
                    <div>
                        <label class="{{ $kelasLabel }}">Jumlah</label>
                        <input type="number"
                               name="items[{{ $indeks }}][jumlah]"
                               data-jumlah
                               min="1"
                               step="1"
                               value="{{ $item['jumlah'] ?? 1 }}"
                               class="{{ $kelasInput }}">
                    </div>

                    <div>
                        <label class="{{ $kelasLabel }}">Harga satuan</label>
                        <input type="number"
                               name="items[{{ $indeks }}][harga_satuan]"
                               data-harga
                               min="0"
                               step="any"
                               inputmode="decimal"
                               value="{{ $item['harga_satuan'] ?? '' }}"
                               class="{{ $kelasInput }}">
                    </div>
                </div>

                <p class="mt-2 text-right text-xs text-slate-500">
                    Subtotal <span data-subtotal class="font-bold text-slate-800">{{ $rupiah((int) ($item['jumlah'] ?? 0) * (float) ($item['harga_satuan'] ?? 0)) }}</span>
                </p>
            </div>
        @endforeach
    </div>

    <div class="mt-3 flex items-center justify-between rounded-xl bg-slate-900 px-4 py-3 text-white">
        <span class="text-xs font-semibold uppercase tracking-wide text-slate-300">Total pesanan</span>
        <span id="total-preview" class="text-lg font-bold">{{ $rupiah($totalAwal) }}</span>
    </div>
</section>

{{-- Catatan & status --}}
<section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="space-y-3">
        <div>
            <label for="catatan_khusus" class="{{ $kelasLabel }}">Catatan khusus <span class="font-normal normal-case text-slate-400">(opsional)</span></label>
            <textarea id="catatan_khusus"
                      name="catatan_khusus"
                      rows="3"
                      maxlength="1000"
                      placeholder="Contoh: tanpa sambal, antar pukul 10.00"
                      class="{{ $kelasInput }}">{{ old('catatan_khusus', $isEdit ? $pesanan->catatan_khusus : '') }}</textarea>
        </div>

        @if ($isEdit)
            <div>
                <label for="status" class="{{ $kelasLabel }}">Status pesanan</label>

                @if ($pesanan->status === 'batal')
                    <input type="hidden" name="status" value="batal">
                    <p class="rounded-xl bg-rose-50 px-3 py-2.5 text-sm font-semibold text-rose-700">
                        Pesanan sudah dibatalkan. Status tidak dapat diubah lagi.
                    </p>
                @else
                    <select id="status" name="status" class="{{ $kelasInput }}">
                        @foreach (['baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai'] as $nilai => $label)
                            <option value="{{ $nilai }}" @selected(old('status', $pesanan->status) === $nilai)>{{ $label }}</option>
                        @endforeach

                        @if (auth()->user()->isPemilik())
                            <option value="batal" @selected(old('status', $pesanan->status) === 'batal')>
                                Batal (DP hangus jika H-1)
                            </option>
                        @endif
                    </select>
                    <p class="mt-1 text-[11px] text-slate-400">
                        Membatalkan pesanan dapat membuat DP hangus. Hanya Pemilik yang boleh membatalkan.
                    </p>
                @endif
            </div>
        @endif
    </div>
</section>

{{-- Tombol aksi --}}
<div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
    <a href="{{ $isEdit ? route('pesanan.show', $pesanan) : route('pesanan.index') }}"
       class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
        Batal
    </a>

    <button type="submit"
            class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-orange-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm shadow-orange-600/30 transition hover:bg-orange-700">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
        {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Pesanan' }}
    </button>
</div>

{{-- Baris item untuk ditambahkan lewat JS --}}
<template id="item-row-template">
    <div class="item-row rounded-xl border border-slate-200 bg-slate-50/60 p-3">
        <div class="flex items-center justify-between gap-2">
            <span class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Item <span data-nomor>1</span></span>
            <button type="button"
                    data-hapus
                    class="rounded-md px-2 py-0.5 text-[11px] font-semibold text-rose-600 transition hover:bg-rose-50">
                Hapus
            </button>
        </div>

        <div class="mt-2">
            <label class="{{ $kelasLabel }}">Menu</label>
            <select name="items[__INDEX__][menu_id]" data-menu class="{{ $kelasInput }}">
                <option value="">— Pilih menu —</option>
                @foreach ($menus as $menu)
                    <option value="{{ $menu->id }}">{{ $menu->nama_menu }} · {{ $rupiah($menu->harga_standar) }}</option>
                @endforeach
            </select>
        </div>

        <div class="mt-2 grid grid-cols-2 gap-2">
            <div>
                <label class="{{ $kelasLabel }}">Jumlah</label>
                <input type="number"
                       name="items[__INDEX__][jumlah]"
                       data-jumlah
                       min="1"
                       step="1"
                       value="1"
                       class="{{ $kelasInput }}">
            </div>

            <div>
                <label class="{{ $kelasLabel }}">Harga satuan</label>
                <input type="number"
                       name="items[__INDEX__][harga_satuan]"
                       data-harga
                       min="0"
                       step="any"
                       inputmode="decimal"
                       class="{{ $kelasInput }}">
            </div>
        </div>

        <p class="mt-2 text-right text-xs text-slate-500">
            Subtotal <span data-subtotal class="font-bold text-slate-800">Rp 0</span>
        </p>
    </div>
</template>

@push('scripts')
    <script>
        (function () {
            const wrap = document.getElementById('item-rows');
            const template = document.getElementById('item-row-template');
            const totalPreview = document.getElementById('total-preview');
            const tombolTambah = document.getElementById('tambah-item');
            const pilihanPelanggan = document.getElementById('pelanggan_id');
            const blokPelangganBaru = document.getElementById('pelanggan-baru');

            if (!wrap || !template) {
                return;
            }

            const hargaMenu = JSON.parse(wrap.dataset.hargaMenu || '{}');
            let nomorBerikut = parseInt(wrap.dataset.nextIndex || '0', 10);

            const rupiah = (angka) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(angka));

            function hitungBaris(row) {
                const jumlah = parseFloat(row.querySelector('[data-jumlah]').value) || 0;
                const harga = parseFloat(row.querySelector('[data-harga]').value) || 0;
                const subtotal = jumlah * harga;

                row.querySelector('[data-subtotal]').textContent = rupiah(subtotal);

                return subtotal;
            }

            function hitungTotal() {
                let total = 0;

                wrap.querySelectorAll('.item-row').forEach(function (row) {
                    total += hitungBaris(row);
                });

                totalPreview.textContent = rupiah(total);
            }

            function rapikanNomor() {
                const rows = wrap.querySelectorAll('.item-row');

                rows.forEach(function (row, indeks) {
                    row.querySelectorAll('[name]').forEach(function (input) {
                        input.name = input.name.replace(/items\[\d+\]/, 'items[' + indeks + ']');
                    });

                    const label = row.querySelector('[data-nomor]');
                    if (label) {
                        label.textContent = indeks + 1;
                    }
                });

                if (rows.length === 0) {
                    tambahBaris();
                }
            }

            function pasangEvent(row) {
                row.querySelector('[data-menu]').addEventListener('change', function () {
                    const harga = hargaMenu[this.value];

                    if (harga !== undefined) {
                        row.querySelector('[data-harga]').value = Math.round(parseFloat(harga));
                    }

                    hitungTotal();
                });

                row.querySelector('[data-jumlah]').addEventListener('input', hitungTotal);
                row.querySelector('[data-harga]').addEventListener('input', hitungTotal);

                row.querySelector('[data-hapus]').addEventListener('click', function () {
                    row.remove();
                    rapikanNomor();
                    hitungTotal();
                });
            }

            function tambahBaris() {
                const row = template.content.firstElementChild.cloneNode(true);

                row.innerHTML = row.innerHTML.replaceAll('__INDEX__', nomorBerikut);
                nomorBerikut += 1;

                wrap.appendChild(row);
                pasangEvent(row);
                rapikanNomor();
                hitungTotal();
            }

            wrap.querySelectorAll('.item-row').forEach(pasangEvent);
            tombolTambah.addEventListener('click', tambahBaris);

            if (pilihanPelanggan && blokPelangganBaru) {
                const aturPelangganBaru = function () {
                    blokPelangganBaru.classList.toggle('hidden', pilihanPelanggan.value !== '');
                };

                pilihanPelanggan.addEventListener('change', aturPelangganBaru);
                aturPelangganBaru();
            }

            hitungTotal();
        })();
    </script>
@endpush
