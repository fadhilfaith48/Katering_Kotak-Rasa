<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use DateTimeImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalController extends Controller
{
    /** @var array<int, string> */
    private const RENTANG = ['hari-ini', 'minggu-ini', 'semua', 'tanggal'];

    /**
     * Daftar pesanan berdasarkan tanggal acara.
     *
     * Pemilik dan Pencatat sama-sama boleh melihat. Pesanan batal tetap
     * ditampilkan dengan penanda khusus, tetapi tidak masuk beban kerja.
     */
    public function index(Request $request): View
    {
        [$rentang, $tanggal, $mulai, $selesai] = $this->filterTanggal($request);

        $query = Pesanan::query()
            ->with(['pelanggan', 'items.menu'])
            ->when($mulai !== null && $selesai !== null, fn ($query) => $query
                ->whereDate('tanggal_acara', '>=', $mulai)
                ->whereDate('tanggal_acara', '<=', $selesai))
            ->orderBy('tanggal_acara')
            ->orderBy('id');

        $semua = $query->get();
        $grup = $semua->groupBy(fn (Pesanan $pesanan) => $pesanan->tanggal_acara?->toDateString() ?? 'tanpa-tanggal');

        $aktif = $semua->where('status', '!=', 'batal');
        $jumlahPesanan = $aktif->count();
        $jumlahBatal = $semua->count() - $jumlahPesanan;
        $totalBox = $aktif->sum(fn (Pesanan $pesanan) => $this->jumlahJenis($pesanan, 'box'));
        $totalSnack = $aktif->sum(fn (Pesanan $pesanan) => $this->jumlahJenis($pesanan, 'snack'));

        return view('jadwal.index', compact(
            'rentang',
            'tanggal',
            'mulai',
            'selesai',
            'grup',
            'jumlahPesanan',
            'jumlahBatal',
            'totalBox',
            'totalSnack',
        ));
    }

    /**
     * Normalisasi filter menjadi [rentang, tanggal, mulai, selesai].
     */
    private function filterTanggal(Request $request): array
    {
        $tanggal = $this->tanggalValid($request->query('tanggal'));
        $rentang = $request->query('rentang');

        // Input tanggal tertentu lebih spesifik daripada pilihan rentang.
        if ($tanggal !== null) {
            $rentang = 'tanggal';
        }

        if (! is_string($rentang) || ! in_array($rentang, self::RENTANG, true)) {
            $rentang = 'minggu-ini';
        }

        if ($rentang === 'tanggal') {
            $tanggal ??= today()->toDateString();

            return [$rentang, $tanggal, $tanggal, $tanggal];
        }

        if ($rentang === 'hari-ini') {
            $hariIni = today()->toDateString();

            return [$rentang, null, $hariIni, $hariIni];
        }

        if ($rentang === 'minggu-ini') {
            return [
                $rentang,
                null,
                today()->startOfWeek()->toDateString(),
                today()->endOfWeek()->toDateString(),
            ];
        }

        return [$rentang, null, null, null];
    }

    /**
     * Terima hanya tanggal YYYY-MM-DD yang benar-benar ada.
     */
    private function tanggalValid(mixed $nilai): ?string
    {
        if (! is_string($nilai) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $nilai) !== 1) {
            return null;
        }

        $tanggal = DateTimeImmutable::createFromFormat('!Y-m-d', $nilai);

        return $tanggal && $tanggal->format('Y-m-d') === $nilai ? $nilai : null;
    }

    /**
     * Jumlah item untuk satu jenis menu (box atau snack).
     */
    private function jumlahJenis(Pesanan $pesanan, string $jenis): int
    {
        return (int) $pesanan->items
            ->filter(fn ($item) => $item->menu?->jenis === $jenis)
            ->sum('jumlah');
    }
}
