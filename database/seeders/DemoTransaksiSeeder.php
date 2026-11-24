<?php

namespace Database\Seeders;

use App\Models\KategoriPengeluaran;
use App\Models\Menu;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Data transaksi demo untuk QA, uji perhitungan, dan presentasi.
 *
 * Tidak dipanggil otomatis oleh DatabaseSeeder. Jalankan manual:
 *   php artisan db:seed --class=DemoTransaksiSeeder
 *
 * Semua data ditandai [DEMO] sehingga aman dihapus/diulang tanpa
 * menyentuh data asli.
 */
class DemoTransaksiSeeder extends Seeder
{
    private const PENANDA = '[DEMO]';

    public function run(): void
    {
        $pemilik = User::where('role', 'pemilik')->first() ?? User::first();

        $rina = Pelanggan::firstOrCreate(
            ['nama' => 'Ibu Rina (Banjarsari)'],
            ['no_telepon_wa' => '081234567890', 'catatan' => 'Langganan arisan RT 03'],
        );

        $bambang = Pelanggan::firstOrCreate(
            ['nama' => 'Pak Bambang (Kelurahan)'],
            ['no_telepon_wa' => '081298765432', 'catatan' => 'Sering pesan snack box rapat kantor'],
        );

        $ayamBakar = Menu::firstOrCreate(
            ['nama_menu' => 'Nasi Box Ayam Bakar Madu'],
            ['jenis' => 'box', 'harga_standar' => 25000],
        );

        $ayamLengkuas = Menu::firstOrCreate(
            ['nama_menu' => 'Nasi Box Ayam Goreng Lengkuas'],
            ['jenis' => 'box', 'harga_standar' => 23000],
        );

        $tumpeng = Menu::firstOrCreate(
            ['nama_menu' => 'Nasi Tumpeng Mini Komplit'],
            ['jenis' => 'box', 'harga_standar' => 30000],
        );

        $snackLemper = Menu::firstOrCreate(
            ['nama_menu' => 'Snack Box Lemper & Pastel'],
            ['jenis' => 'snack', 'harga_standar' => 12000],
        );

        $snackRisoles = Menu::firstOrCreate(
            ['nama_menu' => 'Snack Box Risoles Mayo & Lumpur'],
            ['jenis' => 'snack', 'harga_standar' => 13000],
        );

        $kategoriBahan = KategoriPengeluaran::firstOrCreate(
            ['nama_kategori' => 'Bahan'],
            ['deskripsi' => 'Belanja bahan baku makanan, bumbu, beras, ayam/daging'],
        );

        $kategoriKemasan = KategoriPengeluaran::firstOrCreate(
            ['nama_kategori' => 'Kemasan'],
            ['deskripsi' => 'Kotak kardus, mika snack, kantong plastik, sendok garpu'],
        );

        $kategoriAntar = KategoriPengeluaran::firstOrCreate(
            ['nama_kategori' => 'Ongkos antar'],
            ['deskripsi' => 'Bensin atau sewa kurir pengantaran pesanan'],
        );

        DB::transaction(function () use (
            $pemilik, $rina, $bambang,
            $ayamBakar, $ayamLengkuas, $tumpeng, $snackLemper, $snackRisoles,
            $kategoriBahan, $kategoriKemasan, $kategoriAntar,
        ) {
            // Bersihkan data demo sebelumnya agar seeder idempotent.
            // Item & pembayaran ikut terhapus lewat ON DELETE CASCADE.
            Pengeluaran::where('keterangan', 'like', self::PENANDA.'%')->delete();
            Pesanan::where('catatan_khusus', 'like', self::PENANDA.'%')->delete();

            $hariIni = today();

            // 1. Pesanan hari ini, sudah diproses dapur, DP masuk.
            $this->pesanan(
                [
                    'pelanggan_id' => $rina->id,
                    'jalur_pesanan' => 'whatsapp',
                    'tanggal_pesan' => $hariIni->copy()->subDays(3)->toDateString(),
                    'tanggal_acara' => $hariIni->toDateString(),
                    'status' => 'diproses',
                    'status_bayar' => 'dp',
                    'catatan_khusus' => self::PENANDA.' Arisan RT 03, antar pukul 10.00.',
                ],
                [
                    [$ayamBakar, 50, 25000],
                    [$snackLemper, 20, 12000],
                ],
                [
                    'tanggal_bayar' => $hariIni->copy()->subDays(2)->toDateString(),
                    'nominal' => 500000,
                    'jenis' => 'dp',
                    'catatan' => self::PENANDA.' DP via transfer.',
                ],
            );

            // 2. Pesanan besok, masih baru, DP masuk.
            $this->pesanan(
                [
                    'pelanggan_id' => $bambang->id,
                    'jalur_pesanan' => 'langsung',
                    'tanggal_pesan' => $hariIni->copy()->subDay()->toDateString(),
                    'tanggal_acara' => $hariIni->copy()->addDay()->toDateString(),
                    'status' => 'baru',
                    'status_bayar' => 'dp',
                    'catatan_khusus' => self::PENANDA.' Rapat kelurahan, tanpa sambal.',
                ],
                [
                    [$ayamLengkuas, 30, 23000],
                ],
                [
                    'tanggal_bayar' => $hariIni->toDateString(),
                    'nominal' => 300000,
                    'jenis' => 'dp',
                    'catatan' => self::PENANDA.' DP tunai.',
                ],
            );

            // 3. Pesanan lusa, belum bayar.
            $this->pesanan(
                [
                    'pelanggan_id' => $rina->id,
                    'jalur_pesanan' => 'whatsapp',
                    'tanggal_pesan' => $hariIni->toDateString(),
                    'tanggal_acara' => $hariIni->copy()->addDays(2)->toDateString(),
                    'status' => 'baru',
                    'status_bayar' => 'belum_bayar',
                    'catatan_khusus' => self::PENANDA.' Tumpeng mini untuk syukuran.',
                ],
                [
                    [$tumpeng, 10, 30000],
                    [$snackRisoles, 15, 13000],
                ],
                null,
            );

            // 4. Pesanan batal H-1: DP hangus, tetap masuk pemasukan.
            $this->pesanan(
                [
                    'pelanggan_id' => $bambang->id,
                    'jalur_pesanan' => 'whatsapp',
                    'tanggal_pesan' => $hariIni->copy()->subDays(4)->toDateString(),
                    'tanggal_acara' => $hariIni->copy()->subDay()->toDateString(),
                    'status' => 'batal',
                    'status_bayar' => 'dp',
                    'catatan_khusus' => self::PENANDA.' Dibatalkan H-1, DP hangus.',
                ],
                [
                    [$ayamLengkuas, 20, 23000],
                ],
                [
                    'tanggal_bayar' => $hariIni->copy()->subDays(3)->toDateString(),
                    'nominal' => 200000,
                    'jenis' => 'dp_hangus',
                    'catatan' => self::PENANDA.' DP hangus karena batal H-1.',
                ],
            );

            // Pengeluaran demo bulan ini.
            Pengeluaran::create([
                'tanggal' => $hariIni->copy()->subDay()->toDateString(),
                'keterangan' => self::PENANDA.' Belanja ayam, beras, dan bumbu',
                'kategori_id' => $kategoriBahan->id,
                'nominal' => 350000,
                'user_id' => $pemilik?->id,
            ]);

            Pengeluaran::create([
                'tanggal' => $hariIni->copy()->subDays(3)->toDateString(),
                'keterangan' => self::PENANDA.' Kotak nasi 100 pcs',
                'kategori_id' => $kategoriKemasan->id,
                'nominal' => 150000,
                'user_id' => $pemilik?->id,
            ]);

            Pengeluaran::create([
                'tanggal' => $hariIni->toDateString(),
                'keterangan' => self::PENANDA.' Bensin antar pesanan',
                'kategori_id' => $kategoriAntar->id,
                'nominal' => 50000,
                'user_id' => $pemilik?->id,
            ]);
        });

        $this->command?->info(sprintf(
            'Data demo dibuat: %d pesanan, %d pembayaran, %d pengeluaran.',
            Pesanan::where('catatan_khusus', 'like', self::PENANDA.'%')->count(),
            Pembayaran::where('catatan', 'like', self::PENANDA.'%')->count(),
            Pengeluaran::where('keterangan', 'like', self::PENANDA.'%')->count(),
        ));
    }

    /**
     * Buat satu pesanan lengkap dengan item dan pembayaran opsional.
     *
     * @param  array<string, mixed>  $header
     * @param  array<int, array{0: Menu, 1: int, 2: int}>  $items
     * @param  array<string, mixed>|null  $pembayaran
     */
    private function pesanan(array $header, array $items, ?array $pembayaran): Pesanan
    {
        $pesanan = Pesanan::create($header + ['total' => 0]);
        $total = 0;

        foreach ($items as [$menu, $jumlah, $hargaSatuan]) {
            $subtotal = $jumlah * $hargaSatuan;
            $total += $subtotal;

            $pesanan->items()->create([
                'menu_id' => $menu->id,
                'jumlah' => $jumlah,
                'harga_satuan' => $hargaSatuan,
                'subtotal' => $subtotal,
            ]);
        }

        $pesanan->update(['total' => $total]);

        if ($pembayaran !== null) {
            $pesanan->pembayaran()->create($pembayaran);
        }

        return $pesanan;
    }
}
