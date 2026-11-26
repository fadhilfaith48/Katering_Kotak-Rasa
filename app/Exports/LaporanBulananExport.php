<?php

namespace App\Exports;

use App\Exports\Laporan\KategoriSheet;
use App\Exports\Laporan\RingkasanSheet;
use App\Exports\Laporan\TagihanSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanBulananExport implements WithMultipleSheets
{
    public function __construct(private array $data)
    {
    }

    public function sheets(): array
    {
        return [
            new RingkasanSheet($this->data),
            new KategoriSheet($this->data),
            new TagihanSheet($this->data),
        ];
    }
}
