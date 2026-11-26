<?php

namespace App\Http\Controllers;

use App\Exports\LaporanBulananExport;
use App\Services\LaporanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanExportController extends Controller
{
    public function __construct(private LaporanService $laporan)
    {
    }

    /**
     * Unduh laporan bulanan sebagai file Excel .xlsx.
     */
    public function excel(Request $request)
    {
        $data = $this->data($request);

        return Excel::download(
            new LaporanBulananExport($data),
            'Laporan-KotakRasa-'.$data['bulan'].'.xlsx'
        );
    }

    /**
     * Unduh laporan bulanan sebagai file PDF.
     */
    public function pdf(Request $request)
    {
        $data = $this->data($request);

        return Pdf::loadView('laporan.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->download('Laporan-KotakRasa-'.$data['bulan'].'.pdf');
    }

    /**
     * @return array<string, mixed>
     */
    private function data(Request $request): array
    {
        $bulan = $request->query('bulan');

        return $this->laporan->ringkasan(
            $this->laporan->bulanValid(is_string($bulan) ? $bulan : null)
        );
    }
}
