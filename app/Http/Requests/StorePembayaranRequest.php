<?php

namespace App\Http\Requests;

use App\Models\Pesanan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePembayaranRequest extends FormRequest
{
    /**
     * Pemilik dan Pencatat sama-sama boleh mencatat pembayaran.
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
            'tanggal_bayar' => ['required', 'date', 'before_or_equal:today'],
            'nominal' => ['required', 'numeric', 'min:1', 'max:99999999'],
            'jenis' => ['required', Rule::in(['dp', 'pelunasan'])],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Pastikan nominal tidak melebihi sisa tagihan.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $pesanan = $this->route('pesanan');

            if (! $pesanan instanceof Pesanan) {
                return;
            }

            if ($pesanan->status === 'batal') {
                $validator->errors()->add('nominal', 'Pesanan sudah dibatalkan sehingga tidak bisa menerima pembayaran.');

                return;
            }

            $nominal = (float) $this->input('nominal');

            if ($nominal - $pesanan->sisa_tagihan > 0.005) {
                $validator->errors()->add(
                    'nominal',
                    'Nominal melebihi sisa tagihan (sisa Rp '.number_format($pesanan->sisa_tagihan, 0, ',', '.').').'
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tanggal_bayar.required' => 'Tanggal pembayaran wajib diisi.',
            'tanggal_bayar.date' => 'Format tanggal pembayaran tidak valid.',
            'tanggal_bayar.before_or_equal' => 'Tanggal pembayaran tidak boleh di masa depan.',
            'nominal.required' => 'Nominal pembayaran wajib diisi.',
            'nominal.numeric' => 'Nominal harus berupa angka.',
            'nominal.min' => 'Nominal pembayaran minimal Rp 1.',
            'jenis.required' => 'Jenis pembayaran wajib dipilih.',
            'jenis.in' => 'Jenis pembayaran tidak valid.',
            'catatan.max' => 'Catatan maksimal 500 karakter.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'tanggal_bayar' => 'tanggal pembayaran',
            'nominal' => 'nominal',
            'jenis' => 'jenis pembayaran',
            'catatan' => 'catatan',
        ];
    }
}
