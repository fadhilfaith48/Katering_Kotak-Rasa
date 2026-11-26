<?php

namespace App\Exports\Laporan;

use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RingkasanSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(private array $data)
    {
    }

    public function array(): array
    {
        $laba = (float) $this->data['laba'];

        return [
            ['Laporan Keuangan KotakRasa'],
            ['Periode', $this->data['labelBulan']],
            ['Dibuat', Carbon::now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm')],
            [],
            ['Komponen', 'Nominal (Rp)', 'Keterangan'],
            ['Pemasukan', round((float) $this->data['pemasukan'], 2), 'Termasuk DP dan pelunasan menurut tanggal bayar'],
            ['DP hangus', round((float) $this->data['dpHangus'], 2), 'Pembatalan H-1 tetap dihitung sebagai pemasukan'],
            ['Total pengeluaran', round((float) $this->data['totalPengeluaran'], 2), $this->data['jumlahTransaksi'].' transaksi pada bulan ini'],
            [$laba >= 0 ? 'Laba bersih' : 'Rugi', round(abs($laba), 2), $laba >= 0 ? 'Pemasukan dikurangi pengeluaran' : 'Pengeluaran melebihi pemasukan'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            5 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FBF7F2'],
                ],
            ],
            8 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Ringkasan';
    }
}
