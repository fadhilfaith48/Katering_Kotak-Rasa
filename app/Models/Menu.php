<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menu';

    protected $fillable = [
        'nama_menu',
        'jenis',
        'harga_standar',
    ];

    protected $casts = [
        'harga_standar' => 'decimal:2',
    ];

    /**
     * Relasi: Satu menu bisa ada di banyak pesanan_item.
     */
    public function pesananItem()
    {
        return $this->hasMany(PesananItem::class, 'menu_id');
    }
}
