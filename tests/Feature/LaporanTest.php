<?php

namespace Tests\Feature;

use App\Models\KategoriPengeluaran;
use App\Models\Pembayaran;
use App\Models\Pelanggan;
use App\Models\Pengeluaran;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    private function pengguna(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function kategori(string $nama): KategoriPengeluaran
    {
        return KategoriPengeluaran::create(['nama_kategori' => $nama]);
    }

    private function pesanan(
        string $namaPelanggan,
        float $total,
        string $statusBayar = 'belum_bayar',
        string $status = 'baru',
    ): Pesanan {
        $pelanggan = Pelanggan::create([
            'nama' => $namaPelanggan,
            'no_telepon_wa' => fake()->unique()->numerify('0812########'),
        ]);

        return Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'jalur_pesanan' => 'whatsapp',
            'tanggal_pesan' => today()->toDateString(),
            'tanggal_acara' => today()->addDays(3)->toDateString(),
            'status' => $status,
            'status_bayar' => $statusBayar,
            'total' => $total,
        ]);
    }

    private function bayar(Pesanan $pesanan, float $nominal, string $jenis, ?string $tanggal = null): Pembayaran
    {
        return $pesanan->pembayaran()->create([
            'tanggal_bayar' => $tanggal ?? today()->toDateString(),
            'nominal' => $nominal,
            'jenis' => $jenis,
        ]);
    }

    private function pengeluaran(KategoriPengeluaran $kategori, float $nominal, ?string $tanggal = null): void
    {
        Pengeluaran::create([
            'tanggal' => $tanggal ?? today()->toDateString(),
            'keterangan' => '[UJI] pengeluaran laporan',
            'kategori_id' => $kategori->id,
            'nominal' => $nominal,
        ]);
    }

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->get(route('laporan.index'))
            ->assertRedirect(route('login'));
    }

    public function test_pencatat_tidak_boleh_melihat_laporan_dan_menunya_disembunyikan(): void
    {
        $pencatat = $this->pengguna('pencatat');

        $this->actingAs($pencatat)
            ->get(route('laporan.index'))
            ->assertForbidden();

        $this->actingAs($pencatat)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Laporan');
    }

    public function test_hitungan_manual_pemasukan_pengeluaran_dan_laba_bersih(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $bahan = $this->kategori('Bahan');
        $kemasan = $this->kategori('Kemasan');
        $ongkos = $this->kategori('Ongkos antar');

        $pesanan = $this->pesanan('[UJI] Laporan Pemasukan', 1000000);
        $this->bayar($pesanan, 500000, 'dp');
        $this->bayar($pesanan, 300000, 'pelunasan');
        $this->bayar($pesanan, 200000, 'dp_hangus');

        $this->pengeluaran($bahan, 350000);
        $this->pengeluaran($kemasan, 150000);
        $this->pengeluaran($ongkos, 50000);

        $this->actingAs($pemilik)
            ->get(route('laporan.index'))
            ->assertOk()
            ->assertSee('Laporan')
            ->assertSee('Total pengeluaran')
            ->assertSee('Laba bersih')
            ->assertSee('Rp 1.000.000')
            ->assertSee('Rp 550.000')
            ->assertSee('Rp 450.000')
            ->assertSee('Bahan')
            ->assertSee('Kemasan')
            ->assertSee('Ongkos antar')
            ->assertSee('Termasuk DP hangus Rp 200.000.');
    }

    public function test_filter_bulan_hanya_memasukkan_transaksi_bulan_terpilih(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $kategori = $this->kategori('Bahan');
        $bulanIni = now()->format('Y-m');
        $bulanLalu = now()->subMonth()->format('Y-m');

        $pesananIni = $this->pesanan('[UJI] Bulan Ini', 111000);
        $this->bayar($pesananIni, 111000, 'dp');
        $this->pengeluaran($kategori, 11100);

        $pesananLalu = $this->pesanan('[UJI] Bulan Lalu', 222000);
        $this->bayar($pesananLalu, 222000, 'dp', now()->subMonth()->toDateString());
        $this->pengeluaran($kategori, 22200, now()->subMonth()->toDateString());

        $this->actingAs($pemilik)
            ->get(route('laporan.index', ['bulan' => $bulanIni]))
            ->assertOk()
            ->assertSee('Rp 111.000')
            ->assertSee('Rp 11.100')
            ->assertDontSee('Rp 222.000')
            ->assertDontSee('Rp 22.200');

        $this->actingAs($pemilik)
            ->get(route('laporan.index', ['bulan' => $bulanLalu]))
            ->assertOk()
            ->assertSee('Rp 222.000')
            ->assertSee('Rp 22.200')
            ->assertDontSee('Rp 111.000')
            ->assertDontSee('Rp 11.100');
    }

    public function test_bulan_tidak_valid_jatuh_ke_bulan_berjalan(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $kategori = $this->kategori('Bahan');

        $pesanan = $this->pesanan('[UJI] Bulan Valid', 111000);
        $this->bayar($pesanan, 111000, 'dp');
        $this->pengeluaran($kategori, 11100);

        $this->actingAs($pemilik)
            ->get(route('laporan.index', ['bulan' => '2026-99']))
            ->assertOk()
            ->assertSee('Rp 111.000')
            ->assertSee('Rp 11.100');
    }

    public function test_laba_negatif_ditampilkan_sebagai_rugi(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $kategori = $this->kategori('Bahan');

        $pesanan = $this->pesanan('[UJI] Rugi', 100000);
        $this->bayar($pesanan, 100000, 'dp');
        $this->pengeluaran($kategori, 350000);

        $this->actingAs($pemilik)
            ->get(route('laporan.index'))
            ->assertOk()
            ->assertSee('Rugi')
            ->assertSee('Rp 250.000')
            ->assertDontSee('Laba bersih');
    }

    public function test_tagihan_hanya_pesanan_aktif_yang_belum_lunas(): void
    {
        $pemilik = $this->pengguna('pemilik');

        $lunas = $this->pesanan('[UJI] Sudah Lunas', 500000, 'lunas');
        $this->bayar($lunas, 500000, 'pelunasan');

        $dp = $this->pesanan('[UJI] Masih DP', 500000, 'dp');
        $this->bayar($dp, 200000, 'dp');

        $belum = $this->pesanan('[UJI] Belum Bayar', 400000);

        $batal = $this->pesanan('[UJI] Dibatalkan', 900000, 'dp', 'batal');
        $this->bayar($batal, 100000, 'dp_hangus');

        $this->actingAs($pemilik)
            ->get(route('laporan.index'))
            ->assertOk()
            ->assertSee('2 pesanan')
            ->assertSee('[UJI] Masih DP')
            ->assertSee('[UJI] Belum Bayar')
            ->assertSee('Rp 300.000')
            ->assertSee('Rp 400.000')
            ->assertSee('Rp 700.000')
            ->assertDontSee('[UJI] Sudah Lunas')
            ->assertDontSee('[UJI] Dibatalkan');
    }

    public function test_dp_hangus_tidak_mengurangi_sisa_tagihan_pesanan_batal(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $batal = $this->pesanan('[UJI] Hangus', 900000, 'dp', 'batal');
        $this->bayar($batal, 100000, 'dp_hangus');

        $this->actingAs($pemilik)
            ->get(route('laporan.index'))
            ->assertOk()
            ->assertSee('Rp 100.000')
            ->assertSee('Semua tagihan lunas')
            ->assertDontSee('[UJI] Hangus');
    }

    public function test_dashboard_tetap_mengecualikan_dp_hangus(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $pesanan = $this->pesanan('[UJI] Dashboard', 1000000);
        $this->bayar($pesanan, 500000, 'dp');
        $this->bayar($pesanan, 200000, 'dp_hangus');

        $this->actingAs($pemilik)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Rp 500.000')
            ->assertDontSee('Rp 700.000');
    }
}
