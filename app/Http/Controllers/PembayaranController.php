<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePembayaranRequest;
use App\Http\Requests\UpdatePembayaranRequest;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Services\PesananService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function __construct(private readonly PesananService $pesananService)
    {
    }

    /**
     * Catat pembayaran DP atau pelunasan pada satu pesanan.
     */
    public function store(StorePembayaranRequest $request, Pesanan $pesanan): RedirectResponse
    {
        $data = $request->validated();

        $pesanan->pembayaran()->create($data);
        $this->pesananService->sinkronStatusBayar($pesanan);

        return redirect()
            ->route('pesanan.show', $pesanan)
            ->with('success', 'Pembayaran Rp '.number_format((float) $data['nominal'], 0, ',', '.').' berhasil dicatat.');
    }

    /**
     * Ubah pembayaran. Hanya Pemilik (dijaga di FormRequest).
     */
    public function update(UpdatePembayaranRequest $request, Pembayaran $pembayaran): RedirectResponse
    {
        $pembayaran->update($request->validated());
        $this->pesananService->sinkronStatusBayar($pembayaran->pesanan);

        return redirect()
            ->route('pesanan.show', $pembayaran->pesanan_id)
            ->with('success', 'Pembayaran berhasil diperbarui.');
    }

    /**
     * Hapus pembayaran. Hanya Pemilik.
     */
    public function destroy(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        if (! $request->user()->isPemilik()) {
            abort(403, 'Hanya Pemilik yang dapat menghapus pembayaran.');
        }

        if ($pembayaran->jenis === 'dp_hangus') {
            return redirect()
                ->route('pesanan.show', $pembayaran->pesanan_id)
                ->with('error', 'DP hangus tercatat otomatis saat pembatalan dan tidak dapat dihapus.');
        }

        $pesanan = $pembayaran->pesanan;
        $nominal = (float) $pembayaran->nominal;

        $pembayaran->delete();
        $this->pesananService->sinkronStatusBayar($pesanan);

        return redirect()
            ->route('pesanan.show', $pesanan)
            ->with('success', 'Pembayaran Rp '.number_format($nominal, 0, ',', '.').' berhasil dihapus.');
    }
}
