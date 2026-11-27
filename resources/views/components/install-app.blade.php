@props(['kelas' => 'mt-3'])

{{-- Tombol ini hanya ditampilkan oleh JavaScript bila browser mendukung beforeinstallprompt. --}}
<div data-install-wrapper class="{{ $kelas }}">
    <button type="button"
            data-install-app
            class="hidden min-h-11 w-full items-center justify-center gap-2 rounded-kr-sm border border-kr-line-strong bg-white px-3 py-2.5 text-xs font-bold text-kr-brown transition hover:border-kr-primary/40 hover:bg-kr-soft active:scale-[0.99]">
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v2.25A1.75 1.75 0 0 0 5.75 21h12.5A1.75 1.75 0 0 0 20 19.25V17" />
        </svg>
        <span>Pasang Aplikasi</span>
    </button>

    <p data-install-hint class="hidden text-[11px] leading-relaxed text-kr-faint">
        Di iPhone: ketuk tombol Bagikan, lalu pilih “Tambah ke Layar Utama”.
    </p>
</div>
