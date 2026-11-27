<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    private function manifest(): array
    {
        $isi = file_get_contents(public_path('manifest.webmanifest'));

        return json_decode((string) $isi, true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_manifest_memiliki_konfigurasi_pwa_yang_benar(): void
    {
        $manifest = $this->manifest();

        $this->assertSame('KotakRasa — Katering & Invoice', $manifest['name']);
        $this->assertSame('KotakRasa', $manifest['short_name']);
        $this->assertSame('id', $manifest['lang']);
        $this->assertSame('/dashboard', $manifest['start_url']);
        $this->assertSame('/', $manifest['scope']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('#F97316', $manifest['theme_color']);

        $ukuran = array_column($manifest['icons'], 'sizes');
        $this->assertContains('192x192', $ukuran);
        $this->assertContains('512x512', $ukuran);
        $this->assertContains('maskable', array_column($manifest['icons'], 'purpose'));
    }

    public function test_file_ikon_pwa_tersedia(): void
    {
        foreach ([
            'icons/icon-192.png' => [192, 192],
            'icons/icon-512.png' => [512, 512],
            'icons/icon-maskable-512.png' => [512, 512],
            'icons/apple-touch-icon-180.png' => [180, 180],
        ] as $path => [$lebar, $tinggi]) {
            $lokasi = public_path($path);

            $this->assertFileExists($lokasi);

            $ukuran = getimagesize($lokasi);
            $this->assertSame($lebar, $ukuran[0]);
            $this->assertSame($tinggi, $ukuran[1]);
        }
    }

    public function test_halaman_offline_bisa_diakses_tanpa_login(): void
    {
        $this->get(route('offline'))
            ->assertOk()
            ->assertSee('Tidak ada koneksi')
            ->assertSee('Coba lagi');
    }

    public function test_halaman_login_memuat_manifest_dan_tombol_pasang(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('rel="manifest"', false)
            ->assertSee('/manifest.webmanifest')
            ->assertSee('data-install-app', false)
            ->assertSee('Pasang Aplikasi');
    }

    public function test_sidebar_pengguna_juga_memuat_tombol_pasang(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'pemilik']))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('rel="manifest"', false)
            ->assertSee('data-install-app', false);
    }

    public function test_service_worker_hanya_menyimpan_aset_statis(): void
    {
        $sw = (string) file_get_contents(public_path('sw.js'));
        $js = (string) file_get_contents(base_path('resources/js/app.js'));

        $this->assertStringContainsString("request.method !== 'GET'", $sw);
        $this->assertStringContainsString("caches.match(HALAMAN_OFFLINE)", $sw);
        $this->assertStringNotContainsString("'/dashboard'", $sw);
        $this->assertStringNotContainsString("'/laporan'", $sw);
        $this->assertStringNotContainsString("'/login'", $sw);

        $this->assertStringContainsString("register('/sw.js')", $js);
        $this->assertStringContainsString('import.meta.env.PROD', $js);
        $this->assertStringContainsString('beforeinstallprompt', $js);
    }
}
