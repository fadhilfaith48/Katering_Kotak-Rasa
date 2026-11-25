<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePengeluaranRequest;
use App\Http\Requests\UpdatePengeluaranRequest;
use App\Models\KategoriPengeluaran;
use App\Models\Pengeluaran;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengeluaranController extends Controller
{
    /**
     * Daftar pengeluaran dengan filter bulan.
     *
     * Pencatat melihat daftar dan nominal per transaksi, tetapi tidak
     * melihat kartu rekap total. Ringkasan hanya untuk Pemilik.
     */
    public function index(Request $request): View
    {
        $bulan = $this->bulanValid($request);

        $query = Pengeluaran::query()
            ->with(['kategori', 'pencatat'])
            ->when($bulan !== '', function ($query) use ($bulan) {
                [$tahun, $nomorBulan] = explode('-', $bulan);

                $query->whereYear('tanggal', (int) $tahun)
                    ->whereMonth('tanggal', (int) $nomorBulan);
            });

        $totalBulanIni = 0.0;
        $jumlahTransaksi = 0;

        if ($request->user()->isPemilik()) {
            $totalBulanIni = (float) (clone $query)->sum('nominal');
            $jumlahTransaksi = (clone $query)->count();
        }

        $pengeluaran = $query
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('pengeluaran.index', compact(
            'pengeluaran',
            'bulan',
            'totalBulanIni',
            'jumlahTransaksi',
        ));
    }

    /**
     * Form pencatatan pengeluaran baru.
     */
    public function create(): View
    {
        return view('pengeluaran.create', [
            'kategoris' => $this->daftarKategori(),
        ]);
    }

    /**
     * Simpan pengeluaran. Pencatat diisi otomatis dari user yang login.
     */
    public function store(StorePengeluaranRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        Pengeluaran::create($data);

        return redirect()
            ->route('pengeluaran.index')
            ->with('success', 'Pengeluaran Rp '.number_format((float) $data['nominal'], 0, ',', '.').' berhasil dicatat.');
    }

    /**
     * Form ubah pengeluaran. Hanya Pemilik.
     */
    public function edit(Request $request, Pengeluaran $pengeluaran): View
    {
        $this->pastikanPemilik($request, 'mengubah pengeluaran');

        return view('pengeluaran.edit', [
            'pengeluaran' => $pengeluaran,
            'kategoris' => $this->daftarKategori(),
        ]);
    }

    /**
     * Perbarui pengeluaran. Hanya Pemilik (dijaga di FormRequest).
     */
    public function update(UpdatePengeluaranRequest $request, Pengeluaran $pengeluaran): RedirectResponse
    {
        $pengeluaran->update($request->validated());

        return redirect()
            ->route('pengeluaran.index')
            ->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    /**
     * Hapus pengeluaran. Hanya Pemilik.
     */
    public function destroy(Request $request, Pengeluaran $pengeluaran): RedirectResponse
    {
        $this->pastikanPemilik($request, 'menghapus pengeluaran');

        $pengeluaran->delete();

        return redirect()
            ->route('pengeluaran.index')
            ->with('success', 'Pengeluaran berhasil dihapus.');
    }

    /**
     * Ambil filter bulan yang aman: YYYY-MM atau '' untuk semua bulan.
     */
    private function bulanValid(Request $request): string
    {
        $bulan = $request->query('bulan');

        // "all" dipakai tautan "Lihat semua bulan". Nilai kosong juga diterima
        // untuk berjaga-jaga bila browser mengirim ?bulan= tanpa nilai.
        if ($bulan === 'all' || $bulan === '') {
            return '';
        }

        if ($bulan === null) {
            return now()->format('Y-m');
        }

        return is_string($bulan) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)
            ? $bulan
            : now()->format('Y-m');
    }

    /**
     * @return Collection<int, KategoriPengeluaran>
     */
    private function daftarKategori(): Collection
    {
        return KategoriPengeluaran::orderBy('nama_kategori')->get();
    }

    /**
     * Tolak aksi Pencatat dengan 403.
     */
    private function pastikanPemilik(Request $request, string $aksi): void
    {
        if (! $request->user()->isPemilik()) {
            abort(403, 'Hanya Pemilik yang dapat '.$aksi.'.');
        }
    }
}
