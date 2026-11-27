@props([
    'judul',
    'deskripsi' => null,
])

<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
    <div class="min-w-0">
        <h1 class="text-2xl font-bold tracking-tight text-kr-ink sm:text-3xl">{{ $judul }}</h1>
        @if ($deskripsi)
            <p class="mt-1.5 max-w-2xl text-sm leading-relaxed text-kr-muted">{{ $deskripsi }}</p>
        @endif
    </div>

    @isset($aksi)
        <div class="shrink-0">{{ $aksi }}</div>
    @endisset
</div>
