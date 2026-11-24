@props([
    'judul',
    'deskripsi' => null,
])

<div class="mb-5 flex items-start justify-between gap-3">
    <div class="min-w-0">
        <h1 class="text-xl font-bold tracking-tight text-slate-900">{{ $judul }}</h1>
        @if ($deskripsi)
            <p class="mt-1 text-sm leading-relaxed text-slate-500">{{ $deskripsi }}</p>
        @endif
    </div>

    @isset($aksi)
        <div class="shrink-0">{{ $aksi }}</div>
    @endisset
</div>
