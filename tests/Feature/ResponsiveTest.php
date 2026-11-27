<?php

namespace Tests\Feature;

use App\Models\KategoriPengeluaran;
use App\Models\Pelanggan;
use App\Models\Pengeluaran;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResponsiveTest extends TestCase
{
    use RefreshDatabase;

    private function pengguna(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    public function test_tampilan_desktop_memakai_navigasi_samping_dan_konten_lebar(): void
    {
        $this->actingAs($this->pengguna('pemilik'))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Navigasi desktop')
            ->assertSee('Navigasi utama')
            ->assertSee('Navigasi samping')
            ->assertSee('lg:max-w-6xl')
            ->assertSee('lg:grid-cols-4')
            ->assertSee('lg:hidden');
    }

    public function test_menu_laporan_hanya_tampil_di_navigasi_pemilik(): void
    {
        $this->actingAs($this->pengguna('pemilik'))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Navigasi desktop')
            ->assertSee('Laporan');

        $this->actingAs($this->pengguna('pencatat'))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Navigasi desktop')
            ->assertDontSee('Laporan');
    }

    public function test_daftar_memakai_tabel_data_dengan_label_untuk_hp(): void
    {
        $pemilik = $this->pengguna('pemilik');

        $pelanggan = Pelanggan::create([
            'nama' => '[UJI] Tabel Responsif',
            'no_telepon_wa' => '081200000001',
        ]);

        Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'jalur_pesanan' => 'whatsapp',
            'tanggal_pesan' => today()->toDateString(),
            'tanggal_acara' => today()->addDay()->toDateString(),
            'status' => 'baru',
            'status_bayar' => 'belum_bayar',
            'total' => 250000,
        ]);

        $kategori = KategoriPengeluaran::create(['nama_kategori' => 'Bahan']);

        Pengeluaran::create([
            'tanggal' => today()->toDateString(),
            'kategori_id' => $kategori->id,
            'keterangan' => '[UJI] Belanja tabel',
            'nominal' => 50000,
            'user_id' => $pemilik->id,
        ]);

        foreach (['pesanan.index', 'jadwal.index', 'pengeluaran.index', 'laporan.index'] as $rute) {
            $this->actingAs($pemilik)
                ->get(route($rute))
                ->assertOk()
                ->assertSee('kr-table')
                ->assertSee('data-label');
        }
    }
}
