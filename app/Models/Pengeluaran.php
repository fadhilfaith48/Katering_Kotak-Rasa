<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    use HasFactory;

    protected $table = 'pengeluaran';

    protected $fillable = [
        'tanggal',
        'keterangan',
        'kategori_id',
        'nominal',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    /**
     * Relasi ke Kategori Pengeluaran.
     */
    public function kategori()
    {
        return $this->belongsTo(KategoriPengeluaran::class, 'kategori_id');
    }

    /**
     * Relasi ke user/pencatat pengeluaran.
     */
    public function pencatat()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
