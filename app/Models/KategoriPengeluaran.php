<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriPengeluaran extends Model
{
    use HasFactory;

    protected $table = 'kategori_pengeluaran';

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
    ];

    /**
     * Relasi: Satu kategori bisa memiliki banyak transaksi pengeluaran.
     */
    public function pengeluaran()
    {
        return $this->hasMany(Pengeluaran::class, 'kategori_id');
    }
}
