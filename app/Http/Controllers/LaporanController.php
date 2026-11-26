<?php

namespace App\Http\Controllers;

use App\Services\LaporanService;
use App\Services\RingkasanAiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function __construct(
        private LaporanService $laporan,
        private RingkasanAiService $ai,
    ) {
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

    /**
     * Buat ringkasan AI untuk bulan yang sedang dilihat.
     *
     * Kegagalan API tidak mengubah data laporan; pengguna diberi pesan netral.
     */
    public function ringkasanAi(Request $request): RedirectResponse
    {
        $bulanMentah = $request->input('bulan');
        $bulan = $this->laporan->bulanValid(is_string($bulanMentah) ? $bulanMentah : null);
        $data = $this->laporan->ringkasan($bulan);

        $teks = $this->ai->buat($data);

        if ($teks === null) {
            return redirect()->route('laporan.index', ['bulan' => $bulan])
                ->with('ringkasan_ai_error', 'Ringkasan AI belum bisa dibuat. Laporan tetap dapat digunakan.');
        }

        return redirect()->route('laporan.index', ['bulan' => $bulan])
            ->with('ringkasan_ai', [
                'bulan' => $bulan,
                'teks' => $teks,
                'waktu' => now()->locale('id')->isoFormat('D MMM YYYY, HH:mm'),
            ]);
    }
}
