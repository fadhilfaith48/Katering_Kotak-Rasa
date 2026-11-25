<?php

namespace Tests\Feature;

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

    public function test_daftar_dan_laporan_memakai_grid_dua_kolom_di_desktop(): void
    {
        $pemilik = $this->pengguna('pemilik');

        foreach (['pesanan.index', 'jadwal.index', 'pengeluaran.index', 'laporan.index'] as $rute) {
            $this->actingAs($pemilik)
                ->get(route($rute))
                ->assertOk()
                ->assertSee('lg:grid-cols-2');
        }
    }
}
