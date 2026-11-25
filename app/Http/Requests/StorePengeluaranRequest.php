<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengeluaranRequest extends FormRequest
{
    /**
     * Pemilik dan Pencatat sama-sama boleh mencatat pengeluaran.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'kategori_id' => ['required', 'integer', 'exists:kategori_pengeluaran,id'],
            'keterangan' => ['required', 'string', 'max:255'],
            'nominal' => ['required', 'numeric', 'min:1', 'max:9999999999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal pengeluaran wajib diisi.',
            'tanggal.date' => 'Format tanggal pengeluaran tidak valid.',
            'tanggal.before_or_equal' => 'Tanggal pengeluaran tidak boleh di masa depan.',
            'kategori_id.required' => 'Kategori wajib dipilih.',
            'kategori_id.integer' => 'Kategori tidak valid.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak ditemukan.',
            'keterangan.required' => 'Keterangan wajib diisi.',
            'keterangan.string' => 'Keterangan harus berupa teks.',
            'keterangan.max' => 'Keterangan maksimal 255 karakter.',
            'nominal.required' => 'Nominal pengeluaran wajib diisi.',
            'nominal.numeric' => 'Nominal harus berupa angka.',
            'nominal.min' => 'Nominal pengeluaran minimal Rp 1.',
            'nominal.max' => 'Nominal pengeluaran terlalu besar.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'tanggal' => 'tanggal pengeluaran',
            'kategori_id' => 'kategori',
            'keterangan' => 'keterangan',
            'nominal' => 'nominal',
        ];
    }
}
