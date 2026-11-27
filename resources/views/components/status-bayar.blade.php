@props(['status'])

@php
    $kelas = match ($status) {
        'belum_bayar' => 'bg-kr-danger-soft text-kr-danger ring-kr-danger/20',
        'dp' => 'bg-kr-warning-soft text-kr-warning ring-kr-warning/20',
        'lunas' => 'bg-kr-success-soft text-kr-success-deep ring-kr-success/20',
        default => 'bg-kr-tint text-kr-muted ring-kr-line',
    };

    $label = match ($status) {
        'belum_bayar' => 'Belum bayar',
        'dp' => 'DP',
        'lunas' => 'Lunas',
        default => ucfirst((string) $status),
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 $kelas"]) }}>
    {{ $label }}
</span>
