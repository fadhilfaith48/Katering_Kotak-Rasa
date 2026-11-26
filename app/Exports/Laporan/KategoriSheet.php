<?php

namespace App\Exports\Laporan;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KategoriSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(private array $data)
    {
    }

    public function array(): array
    {
        $baris = [
            ['Pengeluaran per kategori'],
            ['Periode', $this->data['labelBulan']],
            [],
            ['Kategori', 'Total (Rp)', 'Persentase', 'Jumlah transaksi'],
        ];

        $total = (float) $this->data['totalPengeluaran'];

        foreach ($this->data['perKategori'] as $item) {
            $nilai = (float) $item->total;
            $persen = $total > 0 ? (int) round(($nilai / $total) * 100) : 0;

            $baris[] = [
                $item->kategori?->nama_kategori ?? 'Tanpa kategori',
                round($nilai, 2),
                $persen.'%',
                '',
            ];
        }

        if ($this->data['perKategori']->isEmpty()) {
            $baris[] = ['Belum ada pengeluaran pada bulan ini.', 0, '0%', ''];
        }

        $baris[] = ['Total', round($total, 2), '100%', $this->data['jumlahTransaksi'].' transaksi'];

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
        return 'Pengeluaran';
    }
}
