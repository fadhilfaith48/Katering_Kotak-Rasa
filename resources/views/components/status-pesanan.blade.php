@props(['status'])

@php
    $kelas = match ($status) {
        'baru' => 'bg-kr-info-soft text-kr-info ring-kr-info/20',
        'diproses' => 'bg-kr-warning-soft text-kr-warning ring-kr-warning/20',
        'selesai' => 'bg-kr-success-soft text-kr-success-deep ring-kr-success/20',
        'batal' => 'bg-kr-danger-soft text-kr-danger ring-kr-danger/20',
        default => 'bg-kr-tint text-kr-muted ring-kr-line',
    };

    $label = match ($status) {
        'baru' => 'Baru',
        'diproses' => 'Diproses',
        'selesai' => 'Selesai',
        'batal' => 'Batal',
        default => ucfirst((string) $status),
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 $kelas"]) }}>
    {{ $label }}
</span>
