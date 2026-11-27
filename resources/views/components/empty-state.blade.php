@props([
    'judul',
    'deskripsi' => null,
])

<div class="rounded-kr-lg border border-dashed border-kr-line-strong bg-white px-6 py-12 text-center">
    <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-kr-soft text-kr-faint">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5 12 3.75 3.75 7.5m16.5 0L12 11.25M20.25 7.5v9L12 20.25m0-9L3.75 7.5v9L12 20.25" />
        </svg>
    </span>

    <p class="mt-4 text-sm font-semibold text-kr-ink">{{ $judul }}</p>

    @if ($deskripsi)
        <p class="mx-auto mt-1.5 max-w-xs text-sm leading-relaxed text-kr-faint">{{ $deskripsi }}</p>
    @endif

    @if ($slot->isNotEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
