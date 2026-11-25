<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalTest extends TestCase
{
    use RefreshDatabase;

    private function pengguna(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function menu(string $nama, string $jenis): Menu
    {
        return Menu::create([
            'nama_menu' => $nama,
            'jenis' => $jenis,
            'harga_standar' => 25000,
        ]);
    }

    private function pesanan(string $namaPelanggan, string $tanggalAcara, string $status, Menu $menu, int $jumlah): Pesanan
    {
        $pelanggan = Pelanggan::create([
            'nama' => $namaPelanggan,
            'no_telepon_wa' => fake()->unique()->numerify('0812########'),
        ]);

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'jalur_pesanan' => 'whatsapp',
            'tanggal_pesan' => today()->toDateString(),
            'tanggal_acara' => $tanggalAcara,
            'status' => $status,
            'status_bayar' => 'belum_bayar',
            'total' => $jumlah * 25000,
        ]);

        $pesanan->items()->create([
            'menu_id' => $menu->id,
            'jumlah' => $jumlah,
            'harga_satuan' => 25000,
            'subtotal' => $jumlah * 25000,
        ]);

        return $pesanan;
    }

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->get(route('jadwal.index'))
            ->assertRedirect(route('login'));
    }

    public function test_pemilik_dan_pencatat_dapat_melihat_jadwal(): void
    {
        $menu = $this->menu('Nasi Box Ayam', 'box');
        $this->pesanan('[UJI] Pelanggan Jadwal', today()->toDateString(), 'baru', $menu, 10);

        foreach (['pemilik', 'pencatat'] as $role) {
            $this->actingAs($this->pengguna($role))
                ->get(route('jadwal.index'))
                ->assertOk()
                ->assertSee('Jadwal')
                ->assertSee('[UJI] Pelanggan Jadwal')
                ->assertSee('Beban kerja');
        }
    }

    public function test_jadwal_terurut_menurut_tanggal_acara(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $menu = $this->menu('Nasi Box Ayam', 'box');

        $besok = today()->addDay()->toDateString();
        $lusa = today()->addDays(2)->toDateString();

        $this->pesanan('[UJI] Urutan Ketiga', $lusa, 'baru', $menu, 10);
        $this->pesanan('[UJI] Urutan Pertama', today()->toDateString(), 'baru', $menu, 10);
        $this->pesanan('[UJI] Urutan Kedua', $besok, 'baru', $menu, 10);

        $html = $this->actingAs($pemilik)
            ->get(route('jadwal.index', ['rentang' => 'semua']))
            ->assertOk()
            ->assertSee('[UJI] Urutan Pertama')
            ->assertSee('[UJI] Urutan Kedua')
            ->assertSee('[UJI] Urutan Ketiga')
            ->getContent();

        $this->assertLessThan(
            strpos($html, '[UJI] Urutan Kedua'),
            strpos($html, '[UJI] Urutan Pertama'),
        );
        $this->assertLessThan(
            strpos($html, '[UJI] Urutan Ketiga'),
            strpos($html, '[UJI] Urutan Kedua'),
        );
    }

    public function test_filter_hari_ini_hanya_menampilkan_pesanan_hari_ini(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $menu = $this->menu('Nasi Box Ayam', 'box');

        $this->pesanan('[UJI] Hari Ini', today()->toDateString(), 'baru', $menu, 10);
        $this->pesanan('[UJI] Besok', today()->addDay()->toDateString(), 'baru', $menu, 10);

        $this->actingAs($pemilik)
            ->get(route('jadwal.index', ['rentang' => 'hari-ini']))
            ->assertOk()
            ->assertSee('[UJI] Hari Ini')
            ->assertDontSee('[UJI] Besok');
    }

    public function test_filter_tanggal_tertentu(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $menu = $this->menu('Nasi Box Ayam', 'box');
        $besok = today()->addDay()->toDateString();

        $this->pesanan('[UJI] Tanggal Hari Ini', today()->toDateString(), 'baru', $menu, 10);
        $this->pesanan('[UJI] Tanggal Besok', $besok, 'baru', $menu, 10);

        $this->actingAs($pemilik)
            ->get(route('jadwal.index', ['tanggal' => $besok]))
            ->assertOk()
            ->assertSee('[UJI] Tanggal Besok')
            ->assertDontSee('[UJI] Tanggal Hari Ini');
    }

    public function test_filter_minggu_ini_dan_semua(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $menu = $this->menu('Nasi Box Ayam', 'box');

        $this->pesanan('[UJI] Dalam Minggu Ini', today()->toDateString(), 'baru', $menu, 10);
        $this->pesanan('[UJI] Bulan Depan', today()->addMonth()->toDateString(), 'baru', $menu, 10);

        $this->actingAs($pemilik)
            ->get(route('jadwal.index', ['rentang' => 'minggu-ini']))
            ->assertOk()
            ->assertSee('[UJI] Dalam Minggu Ini')
            ->assertDontSee('[UJI] Bulan Depan');

        $this->actingAs($pemilik)
            ->get(route('jadwal.index', ['rentang' => 'semua']))
            ->assertOk()
            ->assertSee('[UJI] Dalam Minggu Ini')
            ->assertSee('[UJI] Bulan Depan');
    }

    public function test_tanggal_tidak_valid_jatuh_ke_default_minggu_ini(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $menu = $this->menu('Nasi Box Ayam', 'box');

        $this->pesanan('[UJI] Tanggal Valid', today()->toDateString(), 'baru', $menu, 10);
        $this->pesanan('[UJI] Bulan Depan', today()->addMonth()->toDateString(), 'baru', $menu, 10);

        $this->actingAs($pemilik)
            ->get(route('jadwal.index', ['tanggal' => '2026-02-30']))
            ->assertOk()
            ->assertSee('[UJI] Tanggal Valid')
            ->assertDontSee('[UJI] Bulan Depan');
    }

    public function test_pesanan_batal_tampil_berbeda_dan_tidak_dihitung(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $menu = $this->menu('Nasi Box Ayam', 'box');
        $tanggal = today()->addDays(3)->toDateString();

        $this->pesanan('[UJI] Pesanan Aktif', $tanggal, 'diproses', $menu, 50);
        $this->pesanan('[UJI] Pesanan Batal', $tanggal, 'batal', $menu, 20);

        $this->actingAs($pemilik)
            ->get(route('jadwal.index', ['tanggal' => $tanggal]))
            ->assertOk()
            ->assertSee('[UJI] Pesanan Aktif')
            ->assertSee('[UJI] Pesanan Batal')
            ->assertSee('Batal')
            ->assertSee('Tidak dihitung')
            ->assertSee('50 box')
            ->assertSee('20 box')
            ->assertDontSee('70 box')
            ->assertSee('1 pesanan batal tidak dihitung.');
    }

    public function test_hanya_menu_box_yang_dihitung_sebagai_box(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $box = $this->menu('Nasi Box Ayam', 'box');
        $snack = $this->menu('Snack Box Lemper', 'snack');
        $tanggal = today()->addDays(4)->toDateString();

        $pesanan = $this->pesanan('[UJI] Box Dan Snack', $tanggal, 'baru', $box, 30);
        $pesanan->items()->create([
            'menu_id' => $snack->id,
            'jumlah' => 20,
            'harga_satuan' => 15000,
            'subtotal' => 300000,
        ]);

        $this->actingAs($pemilik)
            ->get(route('jadwal.index', ['tanggal' => $tanggal]))
            ->assertOk()
            ->assertSee('30 box')
            ->assertSee('20 snack')
            ->assertDontSee('50 box');
    }

    public function test_setiap_baris_memiliki_tautan_detail_pesanan(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $menu = $this->menu('Nasi Box Ayam', 'box');
        $pesanan = $this->pesanan('[UJI] Link Detail', today()->toDateString(), 'baru', $menu, 10);

        $this->actingAs($pemilik)
            ->get(route('jadwal.index', ['rentang' => 'hari-ini']))
            ->assertOk()
            ->assertSee(route('pesanan.show', $pesanan))
            ->assertSee('Detail');
    }
}
