<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePesananRequest extends FormRequest
{
    /**
     * Semua pengguna yang sudah login boleh mencatat pesanan.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Aturan validasi pembuatan pesanan baru.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'pelanggan_id' => ['nullable', 'integer', 'exists:pelanggan,id'],
            'pelanggan_baru_nama' => ['nullable', 'string', 'max:100', 'required_without:pelanggan_id'],
            'pelanggan_baru_wa' => ['nullable', 'string', 'max:30'],

            'jalur_pesanan' => ['required', 'in:langsung,whatsapp'],
            'tanggal_pesan' => ['nullable', 'date'],
            'tanggal_acara' => ['required', 'date', 'after_or_equal:today'],
            'catatan_khusus' => ['nullable', 'string', 'max:1000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'integer', 'exists:menu,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1', 'max:9999'],
            'items.*.harga_satuan' => ['required', 'numeric', 'min:0', 'max:99999999'],
        ];
    }

    /**
     * Pesan validasi dalam bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pelanggan_id.exists' => 'Pelanggan yang dipilih tidak ditemukan.',
            'pelanggan_baru_nama.required_without' => 'Pilih pelanggan atau isi nama pelanggan baru.',
            'pelanggan_baru_nama.max' => 'Nama pelanggan maksimal 100 karakter.',
            'pelanggan_baru_wa.max' => 'Nomor WhatsApp maksimal 30 karakter.',

            'jalur_pesanan.required' => 'Jalur pesanan wajib dipilih.',
            'jalur_pesanan.in' => 'Jalur pesanan tidak valid.',

            'tanggal_acara.required' => 'Tanggal acara wajib diisi.',
            'tanggal_acara.date' => 'Format tanggal acara tidak valid.',
            'tanggal_acara.after_or_equal' => 'Tanggal acara tidak boleh sebelum hari ini.',
            'tanggal_pesan.date' => 'Format tanggal pesan tidak valid.',
            'catatan_khusus.max' => 'Catatan maksimal 1000 karakter.',

            'items.required' => 'Tambahkan minimal satu item pesanan.',
            'items.min' => 'Tambahkan minimal satu item pesanan.',
            'items.*.menu_id.required' => 'Menu wajib dipilih pada setiap item.',
            'items.*.menu_id.exists' => 'Menu yang dipilih tidak ditemukan.',
            'items.*.jumlah.required' => 'Jumlah wajib diisi.',
            'items.*.jumlah.integer' => 'Jumlah harus berupa angka bulat.',
            'items.*.jumlah.min' => 'Jumlah minimal 1.',
            'items.*.harga_satuan.required' => 'Harga satuan wajib diisi.',
            'items.*.harga_satuan.numeric' => 'Harga satuan harus berupa angka.',
            'items.*.harga_satuan.min' => 'Harga satuan tidak boleh negatif.',
        ];
    }

    /**
     * Nama atribut yang lebih mudah dibaca pada pesan validasi.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'pelanggan_id' => 'pelanggan',
            'pelanggan_baru_nama' => 'nama pelanggan baru',
            'pelanggan_baru_wa' => 'nomor WhatsApp pelanggan',
            'jalur_pesanan' => 'jalur pesanan',
            'tanggal_pesan' => 'tanggal pesan',
            'tanggal_acara' => 'tanggal acara',
            'catatan_khusus' => 'catatan',
            'items' => 'item pesanan',
        ];
    }
}
