<?php

namespace App\Exports\Laporan;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TagihanSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(private array $data)
    {
    }

    public function array(): array
    {
        $baris = [
            ['Tagihan belum lunas'],
            ['Periode laporan', $this->data['labelBulan']],
            [],
            ['No', 'Nomor', 'Pelanggan', 'Tanggal acara', 'Total (Rp)', 'Dibayar (Rp)', 'Sisa (Rp)', 'Status'],
        ];

        foreach ($this->data['tagihan'] as $indeks => $item) {
            $baris[] = [
                $indeks + 1,
                $item->nomor,
                $item->pelanggan?->nama ?? 'Pelanggan',
                $item->tanggal_acara?->locale('id')->isoFormat('D MMM YYYY') ?? '-',
                round((float) $item->total, 2),
                round((float) $item->total_dibayar, 2),
                round((float) $item->sisa_tagihan, 2),
                $item->label_status_bayar,
            ];
        }

        if ($this->data['tagihan']->isEmpty()) {
            $baris[] = ['', '', 'Semua tagihan lunas.', '', '', '', 0, ''];
        }

        $baris[] = ['', '', '', 'Total tagihan', '', '', round((float) $this->data['totalTagihan'], 2), ''];
        $baris[] = ['', '', 'Pesanan batal tidak dihitung.', '', '', '', '', ''];

        return $baris;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            4 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FBF7F2'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Tagihan';
    }
}
