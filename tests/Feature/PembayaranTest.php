<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Pembayaran;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\User;
use App\Services\PesananService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembayaranTest extends TestCase
{
    use RefreshDatabase;

    private function pengguna(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function pesananContoh(array $atribut = []): Pesanan
    {
        $pelanggan = Pelanggan::create([
            'nama' => 'Ibu Rina',
            'no_telepon_wa' => '081200000001',
        ]);

        $pesanan = Pesanan::create(array_merge([
            'pelanggan_id' => $pelanggan->id,
            'jalur_pesanan' => 'whatsapp',
            'tanggal_pesan' => today()->toDateString(),
            'tanggal_acara' => today()->addDays(3)->toDateString(),
            'status' => 'baru',
            'status_bayar' => 'belum_bayar',
            'total' => 500000,
        ], $atribut));

        $menu = Menu::create([
            'nama_menu' => 'Nasi Box Ayam Bakar',
            'jenis' => 'box',
            'harga_standar' => 25000,
        ]);

        $pesanan->items()->create([
            'menu_id' => $menu->id,
            'jumlah' => 20,
            'harga_satuan' => 25000,
            'subtotal' => 500000,
        ]);

        return $pesanan;
    }

    private function bayar(Pesanan $pesanan, float $nominal, string $jenis = 'dp', ?string $tanggal = null): Pembayaran
    {
        $pembayaran = $pesanan->pembayaran()->create([
            'tanggal_bayar' => $tanggal ?? today()->toDateString(),
            'nominal' => $nominal,
            'jenis' => $jenis,
        ]);

        app(PesananService::class)->sinkronStatusBayar($pesanan);

        return $pembayaran;
    }

    private function dataPembayaran(float $nominal, string $jenis = 'dp', ?string $tanggal = null): array
    {
        return [
            'tanggal_bayar' => $tanggal ?? today()->toDateString(),
            'nominal' => $nominal,
            'jenis' => $jenis,
            'catatan' => 'Transfer BCA',
        ];
    }

    public function test_pencatat_dapat_mencatat_dp_dan_sisa_tagihan_berkurang(): void
    {
        $pencatat = $this->pengguna('pencatat');
        $pesanan = $this->pesananContoh();

        $this->actingAs($pencatat)
            ->post(route('pembayaran.store', $pesanan), $this->dataPembayaran(150000, 'dp'))
            ->assertRedirect(route('pesanan.show', $pesanan))
            ->assertSessionHasNoErrors();

        $pesanan->refresh();

        $this->assertDatabaseHas('pembayaran', [
            'pesanan_id' => $pesanan->id,
            'nominal' => 150000,
            'jenis' => 'dp',
            'catatan' => 'Transfer BCA',
        ]);
        $this->assertSame('dp', $pesanan->status_bayar);
        $this->assertSame(150000.0, $pesanan->total_dibayar);
        $this->assertSame(350000.0, $pesanan->sisa_tagihan);
    }

    public function test_pelunasan_membuat_status_lunas(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $pesanan = $this->pesananContoh();
        $this->bayar($pesanan, 200000, 'dp');

        $this->actingAs($pemilik)
            ->post(route('pembayaran.store', $pesanan), $this->dataPembayaran(300000, 'pelunasan'))
            ->assertSessionHasNoErrors();

        $pesanan->refresh();

        $this->assertSame('lunas', $pesanan->status_bayar);
        $this->assertSame(500000.0, $pesanan->total_dibayar);
        $this->assertSame(0.0, $pesanan->sisa_tagihan);
    }

    public function test_pembayaran_melebihi_sisa_tagihan_ditolak(): void
    {
        $pencatat = $this->pengguna('pencatat');
        $pesanan = $this->pesananContoh();
        $this->bayar($pesanan, 450000, 'dp');

        $this->actingAs($pencatat)
            ->from(route('pesanan.show', $pesanan))
            ->post(route('pembayaran.store', $pesanan), $this->dataPembayaran(50001, 'pelunasan'))
            ->assertRedirect(route('pesanan.show', $pesanan))
            ->assertSessionHasErrors('nominal');

        $this->assertSame(1, $pesanan->pembayaran()->count());
        $this->assertSame('dp', $pesanan->fresh()->status_bayar);
    }

    public function test_tanggal_pembayaran_masa_depan_ditolak(): void
    {
        $pencatat = $this->pengguna('pencatat');
        $pesanan = $this->pesananContoh();

        $this->actingAs($pencatat)
            ->post(route('pembayaran.store', $pesanan), $this->dataPembayaran(100000, 'dp', today()->addDay()->toDateString()))
            ->assertSessionHasErrors('tanggal_bayar');

        $this->assertSame(0, $pesanan->pembayaran()->count());
    }

    public function test_batal_h1_mengubah_dp_menjadi_dp_hangus(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $pesanan = $this->pesananContoh(['tanggal_acara' => today()->addDay()->toDateString()]);
        $this->bayar($pesanan, 150000, 'dp');

        $this->actingAs($pemilik)
            ->post(route('pesanan.batal', $pesanan))
            ->assertRedirect(route('pesanan.show', $pesanan));

        $pesanan->refresh();

        $this->assertSame('batal', $pesanan->status);
        $this->assertDatabaseHas('pembayaran', [
            'id' => $pesanan->pembayaran()->value('id'),
            'jenis' => 'dp_hangus',
            'nominal' => 150000,
        ]);
        $this->assertSame(150000.0, $pesanan->dp_hangus);
        $this->assertSame(0.0, $pesanan->total_dibayar);
        $this->assertSame(0.0, $pesanan->sisa_tagihan);
    }

    public function test_batal_bukan_h1_tidak_menghanguskan_dp(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $pesanan = $this->pesananContoh(['tanggal_acara' => today()->addDays(3)->toDateString()]);
        $pembayaran = $this->bayar($pesanan, 150000, 'dp');

        $this->actingAs($pemilik)
            ->post(route('pesanan.batal', $pesanan))
            ->assertRedirect(route('pesanan.show', $pesanan));

        $this->assertSame('batal', $pesanan->fresh()->status);
        $this->assertSame('dp', $pembayaran->fresh()->jenis);
        $this->assertSame(0.0, $pesanan->fresh()->dp_hangus);
    }

    public function test_pembayaran_pada_pesanan_batal_ditolak(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $pesanan = $this->pesananContoh(['status' => 'batal']);

        $this->actingAs($pemilik)
            ->post(route('pembayaran.store', $pesanan), $this->dataPembayaran(100000))
            ->assertSessionHasErrors('nominal');

        $this->assertSame(0, $pesanan->pembayaran()->count());
    }

    public function test_pencatat_tidak_boleh_mengubah_menghapus_atau_membatalkan(): void
    {
        $pencatat = $this->pengguna('pencatat');
        $pesanan = $this->pesananContoh();
        $pembayaran = $this->bayar($pesanan, 100000, 'dp');

        $this->actingAs($pencatat)
            ->put(route('pembayaran.update', $pembayaran), $this->dataPembayaran(120000))
            ->assertForbidden();

        $this->actingAs($pencatat)
            ->delete(route('pembayaran.destroy', $pembayaran))
            ->assertForbidden();

        $this->actingAs($pencatat)
            ->post(route('pesanan.batal', $pesanan))
            ->assertForbidden();

        $this->assertSame('dp', $pembayaran->fresh()->jenis);
        $this->assertSame(100000.0, (float) $pembayaran->fresh()->nominal);
        $this->assertSame('baru', $pesanan->fresh()->status);
    }

    public function test_dp_hangus_tidak_bisa_diubah_atau_dihapus(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $pesanan = $this->pesananContoh(['status' => 'batal']);
        $pembayaran = $this->bayar($pesanan, 150000, 'dp_hangus');

        $this->actingAs($pemilik)
            ->put(route('pembayaran.update', $pembayaran), $this->dataPembayaran(1000))
            ->assertSessionHasErrors('nominal');

        $this->actingAs($pemilik)
            ->delete(route('pembayaran.destroy', $pembayaran))
            ->assertRedirect(route('pesanan.show', $pesanan))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('pembayaran', [
            'id' => $pembayaran->id,
            'jenis' => 'dp_hangus',
            'nominal' => 150000,
        ]);
    }

    public function test_hapus_pembayaran_mengembalikan_status_bayar(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $pesanan = $this->pesananContoh();

        $this->actingAs($pemilik)
            ->post(route('pembayaran.store', $pesanan), $this->dataPembayaran(500000, 'pelunasan'))
            ->assertSessionHasNoErrors();

        $this->assertSame('lunas', $pesanan->fresh()->status_bayar);

        $pembayaran = $pesanan->pembayaran()->first();

        $this->actingAs($pemilik)
            ->delete(route('pembayaran.destroy', $pembayaran))
            ->assertRedirect(route('pesanan.show', $pesanan));

        $pesanan->refresh();

        $this->assertSame(0, $pesanan->pembayaran()->count());
        $this->assertSame('belum_bayar', $pesanan->status_bayar);
        $this->assertSame(500000.0, $pesanan->sisa_tagihan);
    }

    public function test_ubah_pembayaran_melebihi_sisa_ditolak_dan_yang_valid_mengubah_status(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $pesanan = $this->pesananContoh();
        $pembayaran = $this->bayar($pesanan, 100000, 'dp');

        $this->actingAs($pemilik)
            ->put(route('pembayaran.update', $pembayaran), $this->dataPembayaran(600000))
            ->assertSessionHasErrors('nominal');

        $this->assertSame(100000.0, (float) $pembayaran->fresh()->nominal);

        $this->actingAs($pemilik)
            ->put(route('pembayaran.update', $pembayaran), $this->dataPembayaran(500000, 'pelunasan'))
            ->assertSessionHasNoErrors();

        $pesanan->refresh();

        $this->assertSame('lunas', $pesanan->status_bayar);
        $this->assertSame(0.0, $pesanan->sisa_tagihan);
    }

    public function test_detail_menampilkan_riwayat_pembayaran_dan_form_tambah(): void
    {
        $pencatat = $this->pengguna('pencatat');
        $pesanan = $this->pesananContoh();
        $this->bayar($pesanan, 100000, 'dp');

        $this->actingAs($pencatat)
            ->get(route('pesanan.show', $pesanan))
            ->assertOk()
            ->assertSee('Riwayat Pembayaran')
            ->assertSee('Catat Pembayaran')
            ->assertSee('Sisa tagihan')
            ->assertSee('Rp 400.000')
            ->assertDontSee('Zona berisiko');
    }

    public function test_dashboard_pemilik_tidak_memasukkan_dp_hangus(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $pesanan = $this->pesananContoh([
            'status' => 'batal',
            'tanggal_acara' => today()->addDay()->toDateString(),
        ]);
        $this->bayar($pesanan, 250000, 'dp_hangus');

        $this->actingAs($pemilik)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Rp 0');
    }
}
