<?php

namespace Tests\Feature;

use App\Models\KategoriPengeluaran;
use App\Models\Pengeluaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengeluaranTest extends TestCase
{
    use RefreshDatabase;

    private function pengguna(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function kategori(string $nama = 'Bahan'): KategoriPengeluaran
    {
        return KategoriPengeluaran::create(['nama_kategori' => $nama]);
    }

    /**
     * @param  array<string, mixed>  $tambahan
     * @return array<string, mixed>
     */
    private function dataPengeluaran(?KategoriPengeluaran $kategori = null, array $tambahan = []): array
    {
        return array_merge([
            'tanggal' => today()->toDateString(),
            'kategori_id' => ($kategori ?? $this->kategori())->id,
            'keterangan' => 'Belanja ayam di pasar',
            'nominal' => 350000,
        ], $tambahan);
    }

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->get(route('pengeluaran.index'))
            ->assertRedirect(route('login'));
    }

    public function test_pencatat_dapat_menambah_pengeluaran_dan_pencatatnya_tersimpan(): void
    {
        $pencatat = $this->pengguna('pencatat');
        $kategori = $this->kategori();

        $this->actingAs($pencatat)
            ->post(route('pengeluaran.store'), $this->dataPengeluaran($kategori))
            ->assertRedirect(route('pengeluaran.index'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('pengeluaran', [
            'keterangan' => 'Belanja ayam di pasar',
            'nominal' => 350000,
            'kategori_id' => $kategori->id,
            'user_id' => $pencatat->id,
        ]);
    }

    public function test_nominal_wajib_angka_positif(): void
    {
        $pencatat = $this->pengguna('pencatat');
        $kategori = $this->kategori();

        foreach ([0, -1, 'abc'] as $nominal) {
            $this->actingAs($pencatat)
                ->post(route('pengeluaran.store'), $this->dataPengeluaran($kategori, ['nominal' => $nominal]))
                ->assertSessionHasErrors('nominal');
        }

        $this->assertSame(0, Pengeluaran::count());
    }

    public function test_kategori_dan_keterangan_wajib_diisi(): void
    {
        $pencatat = $this->pengguna('pencatat');
        $kategori = $this->kategori();

        $this->actingAs($pencatat)
            ->post(route('pengeluaran.store'), $this->dataPengeluaran($kategori, [
                'kategori_id' => '',
                'keterangan' => '',
            ]))
            ->assertSessionHasErrors(['kategori_id', 'keterangan']);

        $this->assertSame(0, Pengeluaran::count());
    }

    public function test_tanggal_pengeluaran_masa_depan_ditolak(): void
    {
        $pencatat = $this->pengguna('pencatat');
        $kategori = $this->kategori();

        $this->actingAs($pencatat)
            ->post(route('pengeluaran.store'), $this->dataPengeluaran($kategori, [
                'tanggal' => today()->addDay()->toDateString(),
            ]))
            ->assertSessionHasErrors('tanggal');

        $this->assertSame(0, Pengeluaran::count());
    }

    public function test_pemilik_melihat_kartu_ringkasan_total_pengeluaran(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $kategori = $this->kategori();
        Pengeluaran::create($this->dataPengeluaran($kategori));

        $this->actingAs($pemilik)
            ->get(route('pengeluaran.index'))
            ->assertOk()
            ->assertSee('Total pengeluaran')
            ->assertSee('Rp 350.000')
            ->assertSee('Belanja ayam di pasar')
            ->assertSee('Bahan');
    }

    public function test_pencatat_melihat_daftar_tanpa_ringkasan_total(): void
    {
        $pencatat = $this->pengguna('pencatat');
        $kategori = $this->kategori();
        Pengeluaran::create($this->dataPengeluaran($kategori));

        $this->actingAs($pencatat)
            ->get(route('pengeluaran.index'))
            ->assertOk()
            ->assertSee('Belanja ayam di pasar')
            ->assertSee('Rp 350.000')
            ->assertSee('Bahan')
            ->assertDontSee('Total pengeluaran')
            ->assertDontSee('Ubah')
            ->assertDontSee('Hapus');
    }

    public function test_filter_bulan_hanya_menampilkan_transaksi_bulan_itu(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $kategori = $this->kategori();

        Pengeluaran::create($this->dataPengeluaran($kategori, [
            'keterangan' => '[UJI] bulan berjalan',
            'nominal' => 111000,
        ]));

        Pengeluaran::create($this->dataPengeluaran($kategori, [
            'keterangan' => '[UJI] bulan lalu',
            'tanggal' => today()->subMonth()->toDateString(),
            'nominal' => 222000,
        ]));

        $this->actingAs($pemilik)
            ->get(route('pengeluaran.index', ['bulan' => today()->format('Y-m')]))
            ->assertOk()
            ->assertSee('[UJI] bulan berjalan')
            ->assertDontSee('[UJI] bulan lalu');

        $this->actingAs($pemilik)
            ->get(route('pengeluaran.index', ['bulan' => today()->subMonth()->format('Y-m')]))
            ->assertOk()
            ->assertSee('[UJI] bulan lalu')
            ->assertDontSee('[UJI] bulan berjalan');
    }

    public function test_filter_semua_bulan_menampilkan_semua_transaksi(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $kategori = $this->kategori();

        Pengeluaran::create($this->dataPengeluaran($kategori, ['keterangan' => '[UJI] bulan berjalan']));
        Pengeluaran::create($this->dataPengeluaran($kategori, [
            'keterangan' => '[UJI] bulan lalu',
            'tanggal' => today()->subMonth()->toDateString(),
        ]));

        $this->actingAs($pemilik)
            ->get(route('pengeluaran.index', ['bulan' => 'all']))
            ->assertOk()
            ->assertSee('[UJI] bulan berjalan')
            ->assertSee('[UJI] bulan lalu');
    }

    public function test_filter_bulan_tidak_valid_jatuh_ke_bulan_berjalan(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $kategori = $this->kategori();

        Pengeluaran::create($this->dataPengeluaran($kategori, [
            'keterangan' => '[UJI] bulan berjalan',
        ]));
        Pengeluaran::create($this->dataPengeluaran($kategori, [
            'keterangan' => '[UJI] bulan lalu',
            'tanggal' => today()->subMonth()->toDateString(),
        ]));

        $this->actingAs($pemilik)
            ->get(route('pengeluaran.index', ['bulan' => '2026-99']))
            ->assertOk()
            ->assertSee('[UJI] bulan berjalan')
            ->assertDontSee('[UJI] bulan lalu');
    }

    public function test_pencatat_tidak_boleh_mengubah_atau_menghapus_pengeluaran(): void
    {
        $pencatat = $this->pengguna('pencatat');
        $kategori = $this->kategori();
        $pengeluaran = Pengeluaran::create($this->dataPengeluaran($kategori));

        $this->actingAs($pencatat)
            ->get(route('pengeluaran.edit', $pengeluaran))
            ->assertForbidden();

        $this->actingAs($pencatat)
            ->put(route('pengeluaran.update', $pengeluaran), $this->dataPengeluaran($kategori, [
                'nominal' => 999000,
            ]))
            ->assertForbidden();

        $this->actingAs($pencatat)
            ->delete(route('pengeluaran.destroy', $pengeluaran))
            ->assertForbidden();

        $this->assertDatabaseHas('pengeluaran', [
            'id' => $pengeluaran->id,
            'nominal' => 350000,
        ]);
    }

    public function test_pemilik_dapat_mengubah_dan_menghapus_pengeluaran(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $kategori = $this->kategori();
        $pengeluaran = Pengeluaran::create($this->dataPengeluaran($kategori));

        $this->actingAs($pemilik)
            ->put(route('pengeluaran.update', $pengeluaran), $this->dataPengeluaran($kategori, [
                'keterangan' => 'Belanja bumbu dan sayur',
                'nominal' => 425000,
            ]))
            ->assertRedirect(route('pengeluaran.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pengeluaran', [
            'id' => $pengeluaran->id,
            'keterangan' => 'Belanja bumbu dan sayur',
            'nominal' => 425000,
        ]);

        $this->actingAs($pemilik)
            ->delete(route('pengeluaran.destroy', $pengeluaran))
            ->assertRedirect(route('pengeluaran.index'));

        $this->assertDatabaseMissing('pengeluaran', ['id' => $pengeluaran->id]);
    }

    public function test_form_tambah_dan_ubah_menampilkan_kategori(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $kategori = $this->kategori();
        $pengeluaran = Pengeluaran::create($this->dataPengeluaran($kategori));

        $this->actingAs($pemilik)
            ->get(route('pengeluaran.create'))
            ->assertOk()
            ->assertSee('Pengeluaran Baru')
            ->assertSee('Bahan');

        $this->actingAs($pemilik)
            ->get(route('pengeluaran.edit', $pengeluaran))
            ->assertOk()
            ->assertSee('Ubah Pengeluaran')
            ->assertSee('Bahan');
    }
}
