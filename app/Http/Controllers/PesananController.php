<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePesananRequest;
use App\Http\Requests\UpdatePesananRequest;
use App\Models\Menu;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PesananController extends Controller
{
    /**
     * Daftar pesanan dengan filter status dan pencarian pelanggan.
     */
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $cari = trim($request->string('q')->toString());

        $pesanan = Pesanan::query()
            ->with(['pelanggan', 'items'])
            ->when(in_array($status, Pesanan::STATUS, true), fn ($query) => $query->where('status', $status))
            ->when($cari !== '', function ($query) use ($cari) {
                $kataKunci = '%'.mb_strtolower($cari).'%';

                $query->where(function ($sub) use ($kataKunci, $cari) {
                    $sub->whereHas('pelanggan', function ($pelanggan) use ($kataKunci) {
                        $pelanggan->whereRaw('LOWER(nama) LIKE ?', [$kataKunci]);
                    });

                    if (ctype_digit($cari)) {
                        $sub->orWhere('id', (int) $cari);
                    }
                });
            })
            ->orderByDesc('tanggal_acara')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('pesanan.index', compact('pesanan', 'status', 'cari'));
    }

    /**
     * Form pencatatan pesanan baru.
     */
    public function create(): View
    {
        return view('pesanan.create', [
            'pelanggans' => Pelanggan::orderBy('nama')->get(),
            'menus' => Menu::orderBy('nama_menu')->get(),
        ]);
    }

    /**
     * Simpan pesanan baru beserta itemnya dalam satu transaksi.
     */
    public function store(StorePesananRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $pesanan = DB::transaction(function () use ($data, $request) {
            $pelanggan = $this->resolvePelanggan($data, $request);

            $pesanan = Pesanan::create([
                'pelanggan_id' => $pelanggan->id,
                'jalur_pesanan' => $data['jalur_pesanan'],
                'tanggal_pesan' => $data['tanggal_pesan'] ?? today()->toDateString(),
                'tanggal_acara' => $data['tanggal_acara'],
                'status' => 'baru',
                'status_bayar' => 'belum_bayar',
                'catatan_khusus' => $data['catatan_khusus'] ?? null,
                'total' => 0,
            ]);

            $this->simpanItem($pesanan, $data['items']);

            return $pesanan;
        });

        return redirect()
            ->route('pesanan.show', $pesanan)
            ->with('success', 'Pesanan '.$pesanan->nomor.' berhasil disimpan.');
    }

    /**
     * Detail pesanan beserta item dan ringkasan pembayaran.
     */
    public function show(Pesanan $pesanan): View
    {
        $pesanan->load(['pelanggan', 'items.menu', 'pembayaran']);

        return view('pesanan.show', compact('pesanan'));
    }

    /**
     * Form perubahan pesanan.
     */
    public function edit(Request $request, Pesanan $pesanan): View
    {
        // Pesanan yang sudah dibatalkan hanya boleh disentuh Pemilik.
        if ($pesanan->status === 'batal' && ! $request->user()->isPemilik()) {
            abort(403, 'Pesanan yang sudah dibatalkan hanya dapat diubah oleh Pemilik.');
        }

        $pesanan->load('items');

        return view('pesanan.edit', [
            'pesanan' => $pesanan,
            'pelanggans' => Pelanggan::orderBy('nama')->get(),
            'menus' => Menu::orderBy('nama_menu')->get(),
        ]);
    }

    /**
     * Perbarui pesanan dan ganti seluruh itemnya.
     */
    public function update(UpdatePesananRequest $request, Pesanan $pesanan): RedirectResponse
    {
        if ($pesanan->status === 'batal' && ! $request->user()->isPemilik()) {
            abort(403, 'Pesanan yang sudah dibatalkan hanya dapat diubah oleh Pemilik.');
        }

        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $pesanan) {
            $pelanggan = $this->resolvePelanggan($data, $request);

            $pesanan->update([
                'pelanggan_id' => $pelanggan->id,
                'jalur_pesanan' => $data['jalur_pesanan'],
                'tanggal_pesan' => $data['tanggal_pesan'] ?? $pesanan->tanggal_pesan,
                'tanggal_acara' => $data['tanggal_acara'],
                'status' => $data['status'],
                'catatan_khusus' => $data['catatan_khusus'] ?? null,
            ]);

            // Item diganti utuh agar tidak ada sisa baris lama.
            $pesanan->items()->delete();
            $this->simpanItem($pesanan, $data['items']);
        });

        return redirect()
            ->route('pesanan.show', $pesanan)
            ->with('success', 'Pesanan '.$pesanan->nomor.' berhasil diperbarui.');
    }

    /**
     * Hapus pesanan. Hanya Pemilik dan hanya bila belum ada pembayaran.
     */
    public function destroy(Request $request, Pesanan $pesanan): RedirectResponse
    {
        if (! $request->user()->isPemilik()) {
            abort(403, 'Hanya Pemilik yang dapat menghapus pesanan.');
        }

        if ($pesanan->sudahAdaPembayaran()) {
            return redirect()
                ->route('pesanan.show', $pesanan)
                ->with('error', 'Pesanan sudah memiliki pembayaran sehingga tidak bisa dihapus. Gunakan status Batal.');
        }

        $nomor = $pesanan->nomor;
        $pesanan->delete();

        return redirect()
            ->route('pesanan.index')
            ->with('success', 'Pesanan '.$nomor.' berhasil dihapus.');
    }

    /**
     * Tentukan pelanggan: pakai pilihan yang ada, atau buat pelanggan baru.
     *
     * @param  array<string, mixed>  $data
     */
    private function resolvePelanggan(array $data, Request $request): Pelanggan
    {
        if (! empty($data['pelanggan_id'])) {
            return Pelanggan::findOrFail($data['pelanggan_id']);
        }

        return Pelanggan::firstOrCreate(
            ['nama' => trim((string) $request->input('pelanggan_baru_nama'))],
            ['no_telepon_wa' => $request->input('pelanggan_baru_wa') ?: null],
        );
    }

    /**
     * Simpan item pesanan dan hitung ulang total di server.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    private function simpanItem(Pesanan $pesanan, array $items): void
    {
        $total = 0.0;

        foreach ($items as $item) {
            $jumlah = (int) $item['jumlah'];
            $hargaSatuan = round((float) $item['harga_satuan'], 2);
            $subtotal = $jumlah * $hargaSatuan;
            $total += $subtotal;

            $pesanan->items()->create([
                'menu_id' => (int) $item['menu_id'],
                'jumlah' => $jumlah,
                'harga_satuan' => $hargaSatuan,
                'subtotal' => $subtotal,
            ]);
        }

        // Total selalu dihitung ulang, bukan dari input browser.
        $pesanan->update(['total' => $total]);
    }
}
