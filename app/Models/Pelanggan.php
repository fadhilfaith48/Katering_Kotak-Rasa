<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggan';

    protected $fillable = [
        'nama',
        'no_telepon_wa',
        'catatan',
    ];

    /**
     * Relasi: Satu pelanggan bisa memiliki banyak pesanan.
     */
    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'pelanggan_id');
    }
}
