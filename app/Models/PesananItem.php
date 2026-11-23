<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesananItem extends Model
{
    use HasFactory;

    protected $table = 'pesanan_item';

    protected $fillable = [
        'pesanan_id',
        'menu_id',
        'jumlah',
        'harga_satuan',
        'subtotal',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Relasi ke header pesanan.
     */
    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    /**
     * Relasi ke menu terpilih.
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
