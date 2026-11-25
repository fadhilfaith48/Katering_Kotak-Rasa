<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\PesananItem;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan ringkasan operasional katering.
     */
    public function __invoke(): View
    {
        $user = auth()->user();

        $awalBulan = now()->startOfMonth()->toDateString();
        $akhirBulan = now()->endOfMonth()->toDateString();

        // Pesanan aktif yang acaranya berlangsung bulan ini.
        $totalPesananAktif = Pesanan::query()
            ->whereIn('status', ['baru', 'diproses'])
            ->whereBetween('tanggal_acara', [$awalBulan, $akhirBulan])
            ->count();

        // Box dan snack dihitung terpisah sesuai jenis menu.
        $aktif = fn ($query) => $query->whereIn('status', ['baru', 'diproses']);

        $totalBoxAktif = (int) PesananItem::query()
            ->whereHas('menu', fn ($query) => $query->where('jenis', 'box'))
            ->whereHas('pesanan', $aktif)
            ->sum('jumlah');

        $totalSnackAktif = (int) PesananItem::query()
            ->whereHas('menu', fn ($query) => $query->where('jenis', 'snack'))
            ->whereHas('pesanan', $aktif)
            ->sum('jumlah');

        // Omzet hanya untuk Pemilik (Pencatat tidak boleh melihat nominal uang).
        $omzetBulanIni = 0.0;
        if ($user->isPemilik()) {
            $omzetBulanIni = (float) Pembayaran::query()
                ->whereIn('jenis', ['dp', 'pelunasan'])
                ->whereBetween('tanggal_bayar', [$awalBulan, $akhirBulan])
                ->sum('nominal');
        }

        // Pesanan yang perlu disiapkan hari ini dan besok.
        $pesananTerdekat = Pesanan::query()
            ->with(['pelanggan', 'items.menu'])
            ->where('status', '!=', 'batal')
            ->whereBetween('tanggal_acara', [
                today()->toDateString(),
                today()->addDay()->toDateString(),
            ])
            ->orderBy('tanggal_acara')
            ->orderBy('id')
            ->get();

        return view('dashboard', compact(
            'totalPesananAktif',
            'totalBoxAktif',
            'totalSnackAktif',
            'omzetBulanIni',
            'pesananTerdekat',
        ));
    }
}
