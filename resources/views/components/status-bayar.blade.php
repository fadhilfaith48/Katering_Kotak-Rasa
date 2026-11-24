@props(['status'])

@php
    $kelas = match ($status) {
        'belum_bayar' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'dp' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'lunas' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        default => 'bg-slate-50 text-slate-600 ring-slate-200',
    };

    $label = match ($status) {
        'belum_bayar' => 'Belum bayar',
        'dp' => 'DP',
        'lunas' => 'Lunas',
        default => ucfirst((string) $status),
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ring-1 $kelas"]) }}>
    {{ $label }}
</span>
