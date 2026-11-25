@php
    $isEdit = isset($pengeluaran) && $pengeluaran->exists;

    $kelasInput = 'w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-500/25';
    $kelasLabel = 'mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500';

    $nilaiTanggal = old('tanggal', $isEdit ? $pengeluaran->tanggal?->toDateString() : today()->toDateString());
    $nilaiKategori = old('kategori_id', $isEdit ? (string) $pengeluaran->kategori_id : '');
    $nilaiKeterangan = old('keterangan', $isEdit ? $pengeluaran->keterangan : '');
    $nilaiNominal = old('nominal', $isEdit ? $pengeluaran->nominal : '');
@endphp

{{-- Ringkasan error --}}
@if ($errors->any())
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-sm text-rose-800">
        <p class="font-semibold">Periksa kembali isian berikut:</p>
        <ul class="mt-1 list-inside list-disc space-y-0.5 text-xs">
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <h2 class="text-sm font-bold text-slate-900">Detail Pengeluaran</h2>

    <div class="mt-3 space-y-3">
        <div>
            <label for="tanggal" class="{{ $kelasLabel }}">Tanggal <span class="text-rose-500">*</span></label>
            <input id="tanggal"
                   name="tanggal"
                   type="date"
                   value="{{ $nilaiTanggal }}"
                   max="{{ today()->toDateString() }}"
                   required
                   class="{{ $kelasInput }}">
        </div>

        <div>
            <label for="kategori_id" class="{{ $kelasLabel }}">Kategori <span class="text-rose-500">*</span></label>
            <select id="kategori_id"
                    name="kategori_id"
                    required
                    class="{{ $kelasInput }}">
                <option value="">— Pilih kategori —</option>
                @foreach ($kategoris as $kategori)
                    <option value="{{ $kategori->id }}" @selected($nilaiKategori === (string) $kategori->id)>
                        {{ $kategori->nama_kategori }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="keterangan" class="{{ $kelasLabel }}">Keterangan <span class="text-rose-500">*</span></label>
            <input id="keterangan"
                   name="keterangan"
                   type="text"
                   value="{{ $nilaiKeterangan }}"
                   maxlength="255"
                   placeholder="Contoh: Belanja ayam dan bumbu di pasar"
                   required
                   class="{{ $kelasInput }}">
        </div>

        <div>
            <label for="nominal" class="{{ $kelasLabel }}">Nominal <span class="text-rose-500">*</span></label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-sm font-semibold text-slate-400">Rp</span>
                <input id="nominal"
                       name="nominal"
                       type="number"
                       min="1"
                       step="0.01"
                       inputmode="decimal"
                       value="{{ $nilaiNominal }}"
                       placeholder="0"
                       required
                       class="{{ $kelasInput }} pl-10">
            </div>
        </div>

        @if ($isEdit)
            <p class="rounded-xl bg-slate-50 px-3 py-2 text-[11px] text-slate-500">
                Dicatat oleh <span class="font-semibold text-slate-700">{{ $pengeluaran->pencatat?->name ?? '-' }}</span>
            </p>
        @endif
    </div>
</section>

{{-- Tombol aksi --}}
<div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
    <a href="{{ route('pengeluaran.index') }}"
       class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
        Batal
    </a>

    <button type="submit"
            class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-orange-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm shadow-orange-600/30 transition hover:bg-orange-700">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
        {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Pengeluaran' }}
    </button>
</div>
