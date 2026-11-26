<?php

namespace Tests\Feature;

use App\Exports\LaporanBulananExport;
use App\Models\KategoriPengeluaran;
use App\Models\Pembayaran;
use App\Models\Pelanggan;
use App\Models\Pengeluaran;
use App\Models\Pesanan;
use App\Models\User;
use App\Services\LaporanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanExportTest extends TestCase
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

    private function pesanan(string $namaPelanggan, float $total, string $statusBayar = 'belum_bayar'): Pesanan
    {
        $pelanggan = Pelanggan::create([
            'nama' => $namaPelanggan,
            'no_telepon_wa' => fake()->unique()->numerify('0812########'),
        ]);

        return Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'jalur_pesanan' => 'whatsapp',
            'tanggal_pesan' => today()->toDateString(),
            'tanggal_acara' => today()->addDays(3)->toDateString(),
            'status' => 'baru',
            'status_bayar' => $statusBayar,
            'total' => $total,
        ]);
    }

    private function bayar(Pesanan $pesanan, float $nominal, string $jenis): void
    {
        $pesanan->pembayaran()->create([
            'tanggal_bayar' => today()->toDateString(),
            'nominal' => $nominal,
            'jenis' => $jenis,
        ]);
    }

    private function pengeluaran(KategoriPengeluaran $kategori, float $nominal): void
    {
        Pengeluaran::create([
            'tanggal' => today()->toDateString(),
            'keterangan' => '[UJI] ekspor laporan',
            'kategori_id' => $kategori->id,
            'nominal' => $nominal,
        ]);
    }

    private function siapkanData(): void
    {
        $pesanan = $this->pesanan('[UJI] Ekspor Laporan', 2000000, 'dp');
        $this->bayar($pesanan, 500000, 'dp');
        $this->bayar($pesanan, 300000, 'pelunasan');
        $this->bayar($pesanan, 200000, 'dp_hangus');

        $this->pengeluaran($this->kategori('Bahan'), 350000);
        $this->pengeluaran($this->kategori('Kemasan'), 150000);
        $this->pengeluaran($this->kategori('Ongkos antar'), 50000);
    }

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->get(route('laporan.export.excel'))
            ->assertRedirect(route('login'));

        $this->get(route('laporan.export.pdf'))
            ->assertRedirect(route('login'));
    }

    public function test_pencatat_tidak_boleh_mengunduh_laporan(): void
    {
        $pencatat = $this->pengguna('pencatat');

        $this->actingAs($pencatat)
            ->get(route('laporan.export.excel'))
            ->assertForbidden();

        $this->actingAs($pencatat)
            ->get(route('laporan.export.pdf'))
            ->assertForbidden();
    }

    public function test_halaman_laporan_menampilkan_tombol_ekspor_untuk_pemilik(): void
    {
        $this->actingAs($this->pengguna('pemilik'))
            ->get(route('laporan.index'))
            ->assertOk()
            ->assertSee('Unduh laporan')
            ->assertSee('Excel')
            ->assertSee('PDF')
            ->assertSee(route('laporan.export.excel'))
            ->assertSee(route('laporan.export.pdf'));
    }

    public function test_pemilik_dapat_mengunduh_excel_dengan_angka_yang_sama(): void
    {
        $this->siapkanData();
        $pemilik = $this->pengguna('pemilik');
        $bulan = now()->format('Y-m');

        $response = $this->actingAs($pemilik)
            ->get(route('laporan.export.excel', ['bulan' => $bulan]));

        $response->assertOk();
        $this->assertStringContainsString(
            'Laporan-KotakRasa-'.$bulan.'.xlsx',
            (string) $response->headers->get('Content-Disposition')
        );

        $data = (new LaporanService())->ringkasan($bulan);
        $sheets = (new LaporanBulananExport($data))->sheets();

        $this->assertSame(['Ringkasan', 'Pengeluaran', 'Tagihan'], array_map(
            fn ($sheet) => $sheet->title(),
            $sheets
        ));

        $ringkasan = $sheets[0]->array();
        $this->assertSame('Komponen', $ringkasan[4][0]);
        $this->assertSame('Pemasukan', $ringkasan[5][0]);
        $this->assertEquals(1000000, $ringkasan[5][1]);
        $this->assertEquals(200000, $ringkasan[6][1]);
        $this->assertEquals(550000, $ringkasan[7][1]);
        $this->assertSame('Laba bersih', $ringkasan[8][0]);
        $this->assertEquals(450000, $ringkasan[8][1]);

        $kategori = $sheets[1]->array();
        $this->assertSame('Bahan', $kategori[4][0]);
        $this->assertEquals(350000, $kategori[4][1]);

        $tagihan = $sheets[2]->array();
        $this->assertEquals(1200000, $tagihan[5][6]);
    }

    public function test_pemilik_dapat_mengunduh_pdf(): void
    {
        $this->siapkanData();
        $bulan = now()->format('Y-m');

        $response = $this->actingAs($this->pengguna('pemilik'))
            ->get(route('laporan.export.pdf', ['bulan' => $bulan]));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertStringContainsString(
            'Laporan-KotakRasa-'.$bulan.'.pdf',
            (string) $response->headers->get('Content-Disposition')
        );
        $this->assertStringStartsWith('%PDF', (string) $response->getContent());
    }

    public function test_bulan_tidak_valid_jatuh_ke_bulan_berjalan_saat_ekspor(): void
    {
        $bulan = now()->format('Y-m');

        $response = $this->actingAs($this->pengguna('pemilik'))
            ->get(route('laporan.export.excel', ['bulan' => ' bukan-bulan ']));

        $response->assertOk();
        $this->assertStringContainsString(
            'Laporan-KotakRasa-'.$bulan.'.xlsx',
            (string) $response->headers->get('Content-Disposition')
        );
    }
}
