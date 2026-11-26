<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\Pengeluaran;
use Illuminate\Support\Carbon;

class LaporanService
{
    /**
     * DP hangus tetap pemasukan, walaupun pesanannya dibatalkan.
     *
     * @var array<int, string>
     */
    private const JENIS_PEMASUKAN = ['dp', 'pelunasan', 'dp_hangus'];

    /**
     * Terima hanya bulan YYYY-MM yang valid; selain itu bulan berjalan.
     */
    public function bulanValid(?string $bulan): string
    {
        return is_string($bulan) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan) === 1
            ? $bulan
            : now()->format('Y-m');
    }

    /**
     * Satu sumber perhitungan laporan untuk halaman, Excel, dan PDF.
     *
     * @return array<string, mixed>
     */
    public function ringkasan(string $bulan): array
    {
        [$tahun, $nomorBulan] = explode('-', $bulan);
        $tahun = (int) $tahun;
        $nomorBulan = (int) $nomorBulan;

        $labelBulan = Carbon::createFromDate($tahun, $nomorBulan, 1)
            ->locale('id')
            ->isoFormat('MMMM YYYY');

        $pemasukan = (float) Pembayaran::query()
            ->whereIn('jenis', self::JENIS_PEMASUKAN)
            ->whereYear('tanggal_bayar', $tahun)
            ->whereMonth('tanggal_bayar', $nomorBulan)
            ->sum('nominal');

        $dpHangus = (float) Pembayaran::query()
            ->where('jenis', 'dp_hangus')
            ->whereYear('tanggal_bayar', $tahun)
            ->whereMonth('tanggal_bayar', $nomorBulan)
            ->sum('nominal');

        // Total diambil dari rincian kategori agar angkanya selalu konsisten.
        $perKategori = Pengeluaran::query()
            ->selectRaw('kategori_id, SUM(nominal) as total')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $nomorBulan)
            ->groupBy('kategori_id')
            ->orderByDesc('total')
            ->with('kategori')
            ->get();

        $totalPengeluaran = (float) $perKategori->sum('total');
        $jumlahTransaksi = (int) Pengeluaran::query()
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $nomorBulan)
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

        return compact(
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
        );
    }
}
