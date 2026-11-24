<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesananTest extends TestCase
{
    use RefreshDatabase;

    private function pengguna(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function pelanggan(): Pelanggan
    {
        return Pelanggan::create([
            'nama' => 'Ibu Rina',
            'no_telepon_wa' => '081200000001',
        ]);
    }

    private function menu(string $nama = 'Nasi Box Ayam Bakar', float $harga = 25000): Menu
    {
        return Menu::create([
            'nama_menu' => $nama,
            'jenis' => 'box',
            'harga_standar' => $harga,
        ]);
    }

    private function pesananContoh(array $atribut = []): Pesanan
    {
        $pesanan = Pesanan::create(array_merge([
            'pelanggan_id' => $this->pelanggan()->id,
            'jalur_pesanan' => 'whatsapp',
            'tanggal_pesan' => today()->toDateString(),
            'tanggal_acara' => today()->addDays(2)->toDateString(),
            'status' => 'baru',
            'status_bayar' => 'belum_bayar',
            'total' => 0,
        ], $atribut));

        $pesanan->items()->create([
            'menu_id' => $this->menu()->id,
            'jumlah' => 2,
            'harga_satuan' => 25000,
            'subtotal' => 50000,
        ]);

        $pesanan->update(['total' => 50000]);

        return $pesanan;
    }

    public function test_pencatat_dapat_membuat_pesanan_dengan_pelanggan_yang_ada(): void
    {
        $pencatat = $this->pengguna('pencatat');
        $pelanggan = $this->pelanggan();
        $menu = $this->menu();

        $respons = $this->actingAs($pencatat)->post(route('pesanan.store'), [
            'pelanggan_id' => $pelanggan->id,
            'jalur_pesanan' => 'whatsapp',
            'tanggal_pesan' => today()->toDateString(),
            'tanggal_acara' => today()->addDays(3)->toDateString(),
            'catatan_khusus' => 'Tanpa sambal',
            'total' => 1, // diabaikan, harus dihitung server
            'items' => [
                ['menu_id' => $menu->id, 'jumlah' => 3, 'harga_satuan' => 25000],
            ],
        ]);

        $pesanan = Pesanan::latest('id')->first();
        $respons->assertRedirect(route('pesanan.show', $pesanan));

        $this->assertSame(75000.0, (float) $pesanan->fresh()->total);
        $this->assertSame(1, $pesanan->items()->count());
        $this->assertSame('belum_bayar', $pesanan->status_bayar);
        $this->assertSame('baru', $pesanan->status);
        $this->assertDatabaseCount('pelanggan', 1);
    }

    public function test_pelanggan_baru_disimpan_bersamaan_dengan_pesanan(): void
    {
        $pencatat = $this->pengguna('pencatat');
        $menu = $this->menu();

        $this->actingAs($pencatat)->post(route('pesanan.store'), [
            'pelanggan_id' => '',
            'pelanggan_baru_nama' => 'Pak Bambang',
            'pelanggan_baru_wa' => '081299999999',
            'jalur_pesanan' => 'langsung',
            'tanggal_pesan' => today()->toDateString(),
            'tanggal_acara' => today()->addDay()->toDateString(),
            'items' => [
                ['menu_id' => $menu->id, 'jumlah' => 1, 'harga_satuan' => 25000],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pelanggan', [
            'nama' => 'Pak Bambang',
            'no_telepon_wa' => '081299999999',
        ]);

        $pesanan = Pesanan::latest('id')->first();
        $this->assertSame('Pak Bambang', $pesanan->pelanggan->nama);
    }

    public function test_tanggal_acara_lampau_ditolak(): void
    {
        $pencatat = $this->pengguna('pencatat');
        $menu = $this->menu();

        $this->actingAs($pencatat)
            ->from(route('pesanan.create'))
            ->post(route('pesanan.store'), [
                'pelanggan_id' => $this->pelanggan()->id,
                'jalur_pesanan' => 'whatsapp',
                'tanggal_acara' => today()->subDay()->toDateString(),
                'items' => [
                    ['menu_id' => $menu->id, 'jumlah' => 1, 'harga_satuan' => 25000],
                ],
            ])
            ->assertRedirect(route('pesanan.create'))
            ->assertSessionHasErrors('tanggal_acara');

        $this->assertDatabaseCount('pesanan', 0);
    }

    public function test_pesanan_tanpa_item_ditolak(): void
    {
        $pencatat = $this->pengguna('pencatat');

        $this->actingAs($pencatat)
            ->from(route('pesanan.create'))
            ->post(route('pesanan.store'), [
                'pelanggan_id' => $this->pelanggan()->id,
                'jalur_pesanan' => 'whatsapp',
                'tanggal_acara' => today()->addDay()->toDateString(),
                'items' => [],
            ])
            ->assertSessionHasErrors('items');

        $this->assertDatabaseCount('pesanan', 0);
    }

    public function test_jumlah_item_nol_ditolak(): void
    {
        $pencatat = $this->pengguna('pencatat');
        $menu = $this->menu();

        $this->actingAs($pencatat)
            ->from(route('pesanan.create'))
            ->post(route('pesanan.store'), [
                'pelanggan_id' => $this->pelanggan()->id,
                'jalur_pesanan' => 'whatsapp',
                'tanggal_acara' => today()->addDay()->toDateString(),
                'items' => [
                    ['menu_id' => $menu->id, 'jumlah' => 0, 'harga_satuan' => 25000],
                ],
            ])
            ->assertSessionHasErrors('items.0.jumlah');
    }

    public function test_pencatat_tidak_boleh_menghapus_pesanan(): void
    {
        $pencatat = $this->pengguna('pencatat');
        $pesanan = $this->pesananContoh();

        $this->actingAs($pencatat)
            ->delete(route('pesanan.destroy', $pesanan))
            ->assertForbidden();

        $this->assertDatabaseHas('pesanan', ['id' => $pesanan->id]);
    }

    public function test_pemilik_dapat_menghapus_pesanan_tanpa_pembayaran(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $pesanan = $this->pesananContoh();

        $this->actingAs($pemilik)
            ->delete(route('pesanan.destroy', $pesanan))
            ->assertRedirect(route('pesanan.index'));

        $this->assertDatabaseMissing('pesanan', ['id' => $pesanan->id]);
    }

    public function test_pesanan_yang_sudah_dibayar_tidak_bisa_dihapus(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $pesanan = $this->pesananContoh();

        $pesanan->pembayaran()->create([
            'tanggal_bayar' => today()->toDateString(),
            'nominal' => 100000,
            'jenis' => 'dp',
        ]);

        $this->actingAs($pemilik)
            ->delete(route('pesanan.destroy', $pesanan))
            ->assertRedirect(route('pesanan.show', $pesanan))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('pesanan', ['id' => $pesanan->id]);
    }

    public function test_pencatat_tidak_boleh_membatalkan_pesanan(): void
    {
        $pencatat = $this->pengguna('pencatat');
        $pesanan = $this->pesananContoh();
        $menu = $this->menu();

        $this->actingAs($pencatat)
            ->from(route('pesanan.edit', $pesanan))
            ->put(route('pesanan.update', $pesanan), [
                'pelanggan_id' => $pesanan->pelanggan_id,
                'jalur_pesanan' => 'whatsapp',
                'tanggal_pesan' => $pesanan->tanggal_pesan->toDateString(),
                'tanggal_acara' => $pesanan->tanggal_acara->toDateString(),
                'status' => 'batal',
                'items' => [
                    ['menu_id' => $menu->id, 'jumlah' => 2, 'harga_satuan' => 25000],
                ],
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame('baru', $pesanan->fresh()->status);
    }

    public function test_pemilik_dapat_membatalkan_pesanan(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $pesanan = $this->pesananContoh();
        $menu = $this->menu();

        $this->actingAs($pemilik)
            ->put(route('pesanan.update', $pesanan), [
                'pelanggan_id' => $pesanan->pelanggan_id,
                'jalur_pesanan' => 'whatsapp',
                'tanggal_pesan' => $pesanan->tanggal_pesan->toDateString(),
                'tanggal_acara' => $pesanan->tanggal_acara->toDateString(),
                'status' => 'batal',
                'items' => [
                    ['menu_id' => $menu->id, 'jumlah' => 2, 'harga_satuan' => 25000],
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('batal', $pesanan->fresh()->status);
    }

    public function test_update_menghitung_ulang_total_dan_mengganti_item(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $pesanan = $this->pesananContoh();
        $menu = $this->menu('Snack Box Lemper', 12000);

        $this->actingAs($pemilik)
            ->put(route('pesanan.update', $pesanan), [
                'pelanggan_id' => $pesanan->pelanggan_id,
                'jalur_pesanan' => 'langsung',
                'tanggal_pesan' => $pesanan->tanggal_pesan->toDateString(),
                'tanggal_acara' => $pesanan->tanggal_acara->toDateString(),
                'status' => 'diproses',
                'items' => [
                    ['menu_id' => $menu->id, 'jumlah' => 5, 'harga_satuan' => 10000],
                ],
            ])
            ->assertSessionHasNoErrors();

        $pesanan->refresh();

        $this->assertSame('diproses', $pesanan->status);
        $this->assertSame(50000.0, (float) $pesanan->total);
        $this->assertSame(1, $pesanan->items()->count());
        $this->assertSame(5, (int) $pesanan->items()->first()->jumlah);
    }

    public function test_daftar_pesanan_dapat_difilter_status(): void
    {
        $pemilik = $this->pengguna('pemilik');
        $this->pesananContoh(['status' => 'baru']);
        $this->pesananContoh(['status' => 'selesai']);

        $this->actingAs($pemilik)
            ->get(route('pesanan.index', ['status' => 'selesai']))
            ->assertOk()
            ->assertSee('PSN-000002')
            ->assertDontSee('PSN-000001');
    }
}
