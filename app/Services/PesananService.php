<?php

namespace App\Services;

use App\Models\Pesanan;
use Illuminate\Support\Facades\DB;

class PesananService
{
    /**
     * Hitung ulang status bayar pesanan berdasarkan pembayaran yang masuk.
     *
     * Aturan:
     * - belum ada pembayaran            -> belum_bayar
     * - total dibayar >= total pesanan  -> lunas
     * - selain itu                      -> dp
     *
     * Pesanan berstatus Batal tidak diubah status bayarnya, karena sisa
     * tagihannya sudah ditutup dan nominal yang masuk tetap tercatat.
     */
    public function sinkronStatusBayar(Pesanan $pesanan): void
    {
        if ($pesanan->status === 'batal') {
            return;
        }

        $dibayar = (float) $pesanan->pembayaran()
            ->whereIn('jenis', ['dp', 'pelunasan'])
            ->sum('nominal');

        $total = (float) $pesanan->total;

        $statusBayar = match (true) {
            $dibayar <= 0 => 'belum_bayar',
            $dibayar + 0.005 >= $total => 'lunas',
            default => 'dp',
        };

        $pesanan->forceFill(['status_bayar' => $statusBayar])->save();
    }

    /**
     * Batalkan pesanan dan terapkan aturan DP hangus.
     *
     * Bila pembatalan dilakukan pada H-1 (acara besok), seluruh pembayaran
     * berjenis DP diubah menjadi DP hangus. DP hangus tetap dihitung sebagai
     * pemasukan pada laporan, tetapi tidak lagi mengurangi sisa tagihan.
     */
    public function batalkan(Pesanan $pesanan): void
    {
        DB::transaction(function () use ($pesanan) {
            $pesanan->refresh();

            if ($pesanan->status === 'batal') {
                return;
            }

            $acaraBesok = $pesanan->tanggal_acara?->isTomorrow() ?? false;

            if ($acaraBesok) {
                $pesanan->pembayaran()
                    ->where('jenis', 'dp')
                    ->update(['jenis' => 'dp_hangus']);
            }

            $pesanan->forceFill(['status' => 'batal'])->save();
        });
    }

    /**
     * Total DP yang akan hangus bila pesanan dibatalkan sekarang.
     */
    public function dpAkanHangus(Pesanan $pesanan): float
    {
        if (! $pesanan->tanggal_acara?->isTomorrow()) {
            return 0.0;
        }

        return (float) $pesanan->pembayaran()->where('jenis', 'dp')->sum('nominal');
    }
}
