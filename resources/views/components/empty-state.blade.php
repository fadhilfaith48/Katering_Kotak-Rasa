@props([
    'judul',
    'deskripsi' => null,
])

<div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center">
    <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-slate-50 text-slate-300">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5 12 3.75 3.75 7.5m16.5 0L12 11.25M20.25 7.5v9L12 20.25m0-9L3.75 7.5v9L12 20.25" />
        </svg>
    </span>

    <p class="mt-3 text-sm font-semibold text-slate-600">{{ $judul }}</p>

    @if ($deskripsi)
        <p class="mx-auto mt-1 max-w-xs text-xs leading-relaxed text-slate-400">{{ $deskripsi }}</p>
    @endif

    @if ($slot->isNotEmpty())
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
