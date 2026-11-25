<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class LaporanController extends Controller
{
    /**
     * DP hangus tetap pemasukan, walaupun pesanannya dibatalkan.
     *
     * @var array<int, string>
     */
    private const JENIS_PEMASUKAN = ['dp', 'pelunasan', 'dp_hangus'];

    /**
     * Laporan keuangan bulanan dan daftar tagihan belum lunas.
     *
     * Role sudah dibatasi di route dengan middleware role:pemilik.
     */
    public function index(Request $request): View
    {
        $bulan = $this->bulanValid($request);
        [$tahun, $nomorBulan] = explode('-', $bulan);

        $labelBulan = Carbon::createFromDate((int) $tahun, (int) $nomorBulan, 1)
            ->locale('id')
            ->isoFormat('MMMM YYYY');

        $pemasukan = (float) Pembayaran::query()
            ->whereIn('jenis', self::JENIS_PEMASUKAN)
            ->whereYear('tanggal_bayar', (int) $tahun)
            ->whereMonth('tanggal_bayar', (int) $nomorBulan)
            ->sum('nominal');

        $dpHangus = (float) Pembayaran::query()
            ->where('jenis', 'dp_hangus')
            ->whereYear('tanggal_bayar', (int) $tahun)
            ->whereMonth('tanggal_bayar', (int) $nomorBulan)
            ->sum('nominal');

        // Total diambil dari rincian kategori agar angkanya selalu konsisten.
        $perKategori = Pengeluaran::query()
            ->selectRaw('kategori_id, SUM(nominal) as total')
            ->whereYear('tanggal', (int) $tahun)
            ->whereMonth('tanggal', (int) $nomorBulan)
            ->groupBy('kategori_id')
            ->orderByDesc('total')
            ->with('kategori')
            ->get();

        $totalPengeluaran = (float) $perKategori->sum('total');
        $jumlahTransaksi = (int) Pengeluaran::query()
            ->whereYear('tanggal', (int) $tahun)
            ->whereMonth('tanggal', (int) $nomorBulan)
            ->count();

        $laba = $pemasukan - $totalPengeluaran;

        // Tagihan memakai semua pesanan aktif, tidak dibatasi bulan laporan.
        $tagihan = Pesanan::query()
            ->with('pelanggan')
            ->where('status', '!=', 'batal')
            ->where('status_bayar', '!=', 'lunas')
            ->orderBy('tanggal_acara')
            ->orderBy('id')
            ->get();

        $totalTagihan = (float) $tagihan->sum('sisa_tagihan');

        return view('laporan.index', compact(
            'bulan',
            'labelBulan',
            'pemasukan',
            'dpHangus',
            'perKategori',
            'totalPengeluaran',
            'jumlahTransaksi',
            'laba',
            'tagihan',
            'totalTagihan',
        ));
    }

    /**
     * Terima hanya bulan YYYY-MM yang valid; selain itu bulan berjalan.
     */
    private function bulanValid(Request $request): string
    {
        $bulan = $request->query('bulan');

        return is_string($bulan) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan) === 1
            ? $bulan
            : now()->format('Y-m');
    }
}
