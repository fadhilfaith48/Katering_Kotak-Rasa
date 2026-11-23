<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;

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
}
