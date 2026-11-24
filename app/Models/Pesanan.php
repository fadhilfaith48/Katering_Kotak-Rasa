<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;

    /**
     * Daftar status pesanan yang valid.
     */
    public const STATUS = ['baru', 'diproses', 'selesai', 'batal'];

    protected $table = 'pesanan';

    protected $fillable = [
        'pelanggan_id',
        'jalur_pesanan',
        'tanggal_pesan',
        'tanggal_acara',
        'status',
        'total',
        'status_bayar',
        'catatan_khusus',
    ];

    protected $casts = [
        'tanggal_pesan' => 'date',
        'tanggal_acara' => 'date',
        'total' => 'decimal:2',
    ];

    /**
     * Relasi ke Pelanggan.
     */
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    /**
     * Relasi ke rincian item pesanan.
     */
    public function items()
    {
        return $this->hasMany(PesananItem::class, 'pesanan_id');
    }

    /**
     * Relasi ke catatan riwayat pembayaran.
     */
    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'pesanan_id');
    }

    /**
     * Total yang sudah dibayarkan (DP + Pelunasan normal).
     */
    public function getTotalDibayarAttribute(): float
    {
        return (float) $this->pembayaran()->whereIn('jenis', ['dp', 'pelunasan'])->sum('nominal');
    }

    /**
     * Total DP yang hangus (jika pesanan dibatalkan di H-1).
     */
    public function getDpHangusAttribute(): float
    {
        return (float) $this->pembayaran()->where('jenis', 'dp_hangus')->sum('nominal');
    }

    /**
     * Sisa tagihan otomatis = total pesanan - total pembayaran masuk.
     * Jika status batal, sisa tagihan otomatis 0.
     */
    public function getSisaTagihanAttribute(): float
    {
        if ($this->status === 'batal') {
            return 0.0;
        }

        $sisa = (float) $this->total - $this->total_dibayar;
        return max(0.0, $sisa);
    }

    /**
     * Nomor pesanan untuk ditampilkan, diturunkan dari primary key.
     * Contoh: id 123 -> "PSN-000123".
     */
    public function getNomorAttribute(): string
    {
        return 'PSN-'.str_pad((string) $this->getKey(), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Total box/snack yang dipesan (dari seluruh item).
     */
    public function getJumlahBoxAttribute(): int
    {
        $items = $this->relationLoaded('items') ? $this->items : $this->items()->get();

        return (int) $items->sum('jumlah');
    }

    /**
     * Label status pesanan dalam bahasa Indonesia.
     */
    public function getLabelStatusAttribute(): string
    {
        return match ($this->status) {
            'baru' => 'Baru',
            'diproses' => 'Diproses',
            'selesai' => 'Selesai',
            'batal' => 'Batal',
            default => ucfirst((string) $this->status),
        };
    }

    /**
     * Label status pembayaran dalam bahasa Indonesia.
     */
    public function getLabelStatusBayarAttribute(): string
    {
        return match ($this->status_bayar) {
            'belum_bayar' => 'Belum bayar',
            'dp' => 'DP',
            'lunas' => 'Lunas',
            default => ucfirst((string) $this->status_bayar),
        };
    }

    /**
     * Apakah pesanan sudah memiliki catatan pembayaran.
     */
    public function sudahAdaPembayaran(): bool
    {
        return $this->pembayaran()->exists();
    }
}
