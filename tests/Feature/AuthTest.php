<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function pengguna(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_halaman_login_dapat_dibuka(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke Aplikasi');
    }

    public function test_pemilik_dapat_login_dan_melihat_omzet_serta_menu_laporan(): void
    {
        $pemilik = $this->pengguna('pemilik');

        $this->post('/login', [
            'email' => $pemilik->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($pemilik);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Omzet Bulan Ini')
            ->assertSee(route('laporan.index'));
    }

    public function test_pencatat_tidak_melihat_omzet_dan_tidak_bisa_membuka_laporan(): void
    {
        $pencatat = $this->pengguna('pencatat');

        $this->post('/login', [
            'email' => $pencatat->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Omzet Bulan Ini')
            ->assertDontSee(route('laporan.index'));

        $this->get('/laporan')->assertForbidden();
    }

    public function test_login_gagal_menampilkan_pesan_bahasa_indonesia(): void
    {
        $pemilik = $this->pengguna('pemilik');

        $this->from('/login')
            ->post('/login', [
                'email' => $pemilik->email,
                'password' => 'kata-sandi-salah',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);

        $this->assertGuest();
    }

    public function test_pengguna_dapat_logout(): void
    {
        $pemilik = $this->pengguna('pemilik');

        $this->actingAs($pemilik)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
