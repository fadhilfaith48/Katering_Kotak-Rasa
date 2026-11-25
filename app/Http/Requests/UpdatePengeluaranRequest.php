<?php

namespace App\Http\Requests;

/**
 * Aturan ubah pengeluaran mewarisi aturan tambah pengeluaran.
 * Selisihnya hanya otorisasi: mengubah hanya boleh dilakukan Pemilik.
 */
class UpdatePengeluaranRequest extends StorePengeluaranRequest
{
    /**
     * Hanya Pemilik yang boleh mengubah pengeluaran.
     */
    public function authorize(): bool
    {
        return $this->user()?->isPemilik() ?? false;
    }
}
