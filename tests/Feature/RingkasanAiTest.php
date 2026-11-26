<?php

namespace Tests\Feature;

use App\Models\KategoriPengeluaran;
use App\Models\Pembayaran;
use App\Models\Pelanggan;
use App\Models\Pengeluaran;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RingkasanAiTest extends TestCase
{
    use RefreshDatabase;

    private const NAMA_RAHASIA = '[UJI] Nama Pelanggan Rahasia';

    private const TELEPON_RAHASIA = '081299988877';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.ai.key' => 'kunci-uji',
            'services.ai.model' => 'gemini-3.5-flash-lite',
            'services.ai.base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'services.ai.timeout' => 5,
        ]);
    }

    /**
     * Pasang stub sukses hanya pada test yang memang memanggil API.
     *
     * Http::fake() menambah stub di akhir daftar, jadi memasangnya di setUp()
     * akan mengalahkan fake 429/timeout yang dipasang di dalam test.
     */
    private function fakeSukses(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [['text' => 'Pemasukan Rp 1.000.000 dan laba Rp 450.000.']],
                    ],
                ]],
            ]),
        ]);
    }

    private function pemilik(): User
    {
        return User::factory()->create(['role' => 'pemilik']);
    }

    private function siapkanData(): void
    {
        $pelanggan = Pelanggan::create([
            'nama' => self::NAMA_RAHASIA,
            'no_telepon_wa' => self::TELEPON_RAHASIA,
        ]);

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'jalur_pesanan' => 'whatsapp',
            'tanggal_pesan' => today()->toDateString(),
            'tanggal_acara' => today()->addDays(3)->toDateString(),
            'status' => 'baru',
            'status_bayar' => 'dp',
            'total' => 2000000,
        ]);

        foreach ([['dp', 500000], ['pelunasan', 300000], ['dp_hangus', 200000]] as [$jenis, $nominal]) {
            Pembayaran::create([
                'pesanan_id' => $pesanan->id,
                'tanggal_bayar' => today()->toDateString(),
                'nominal' => $nominal,
                'jenis' => $jenis,
            ]);
        }

        foreach ([['Bahan', 350000], ['Kemasan', 150000], ['Ongkos antar', 50000]] as [$nama, $nominal]) {
            $kategori = KategoriPengeluaran::create(['nama_kategori' => $nama]);

            Pengeluaran::create([
                'tanggal' => today()->toDateString(),
                'keterangan' => '[UJI] ringkasan AI',
                'kategori_id' => $kategori->id,
                'nominal' => $nominal,
            ]);
        }
    }

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->post(route('laporan.ringkasan-ai'))
            ->assertRedirect(route('login'));

        Http::assertNothingSent();
    }

    public function test_pencatat_tidak_boleh_membuat_ringkasan(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'pencatat']))
            ->post(route('laporan.ringkasan-ai'))
            ->assertForbidden();

        Http::assertNothingSent();
    }

    public function test_pemilik_mendapat_ringkasan_bertanda_dibuat_oleh_ai(): void
    {
        $this->fakeSukses();
        $this->siapkanData();
        $pemilik = $this->pemilik();
        $bulan = now()->format('Y-m');

        $this->actingAs($pemilik)
            ->post(route('laporan.ringkasan-ai'), ['bulan' => $bulan])
            ->assertRedirect(route('laporan.index', ['bulan' => $bulan]));

        $this->actingAs($pemilik)
            ->get(route('laporan.index', ['bulan' => $bulan]))
            ->assertOk()
            ->assertSee('Dibuat oleh AI')
            ->assertSee('Pemasukan Rp 1.000.000 dan laba Rp 450.000.')
            ->assertSee('Rp 1.000.000');
    }

    public function test_payload_hanya_berisi_angka_agregat(): void
    {
        $this->fakeSukses();
        $this->siapkanData();
        $bulan = now()->format('Y-m');

        $this->actingAs($this->pemilik())
            ->post(route('laporan.ringkasan-ai'), ['bulan' => $bulan])
            ->assertRedirect();

        Http::assertSent(function ($request): bool {
            $isi = (string) $request->body();

            return $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent'
                && $request->hasHeader('x-goog-api-key', 'kunci-uji')
                && str_contains($isi, '1000000')
                && str_contains($isi, '550000')
                && str_contains($isi, 'Bahan')
                && ! str_contains($isi, self::NAMA_RAHASIA)
                && ! str_contains($isi, self::TELEPON_RAHASIA)
                && ! str_contains($isi, 'PSN-');
        });
    }

    public function test_api_gagal_tidak_mengganggu_laporan(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => 'kuota habis'], 429),
        ]);

        $this->siapkanData();
        $pemilik = $this->pemilik();
        $bulan = now()->format('Y-m');

        $this->actingAs($pemilik)
            ->post(route('laporan.ringkasan-ai'), ['bulan' => $bulan])
            ->assertRedirect(route('laporan.index', ['bulan' => $bulan]));

        $this->actingAs($pemilik)
            ->get(route('laporan.index', ['bulan' => $bulan]))
            ->assertOk()
            ->assertSee('Ringkasan AI belum bisa dibuat. Laporan tetap dapat digunakan.')
            ->assertSee('Rp 1.000.000')
            ->assertDontSee('Dibuat oleh AI');
    }

    public function test_timeout_juga_menghasilkan_pesan_aman(): void
    {
        Http::fake(function () {
            throw new ConnectionException('timeout');
        });

        $this->siapkanData();
        $bulan = now()->format('Y-m');

        $this->actingAs($this->pemilik())
            ->post(route('laporan.ringkasan-ai'), ['bulan' => $bulan])
            ->assertRedirect(route('laporan.index', ['bulan' => $bulan]));

        $this->actingAs($this->pemilik())
            ->get(route('laporan.index', ['bulan' => $bulan]))
            ->assertOk()
            ->assertSee('Ringkasan AI belum bisa dibuat. Laporan tetap dapat digunakan.');
    }

    public function test_tanpa_api_key_tidak_memanggil_api(): void
    {
        config(['services.ai.key' => null]);

        $this->siapkanData();
        $pemilik = $this->pemilik();
        $bulan = now()->format('Y-m');

        $this->actingAs($pemilik)
            ->post(route('laporan.ringkasan-ai'), ['bulan' => $bulan])
            ->assertRedirect();

        Http::assertNothingSent();

        $this->actingAs($pemilik)
            ->get(route('laporan.index', ['bulan' => $bulan]))
            ->assertOk()
            ->assertSee('Ringkasan AI belum bisa dibuat. Laporan tetap dapat digunakan.');
    }

    public function test_bulan_tidak_valid_jatuh_ke_bulan_berjalan(): void
    {
        $this->fakeSukses();
        $bulan = now()->format('Y-m');

        $this->actingAs($this->pemilik())
            ->post(route('laporan.ringkasan-ai'), ['bulan' => ' bukan-bulan '])
            ->assertRedirect(route('laporan.index', ['bulan' => $bulan]));
    }
}
