<?php

namespace App\Http\Controllers;

use App\Services\LaporanService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function __construct(private LaporanService $laporan)
    {
    }

    /**
     * Laporan keuangan bulanan dan daftar tagihan belum lunas.
     *
     * Role sudah dibatasi di route dengan middleware role:pemilik.
     */
    public function index(Request $request): View
    {
        $bulan = $request->query('bulan');
        $data = $this->laporan->ringkasan(
            $this->laporan->bulanValid(is_string($bulan) ? $bulan : null)
        );

        return view('laporan.index', $data);
    }
}
