<?php

namespace App\Http\Requests;

use App\Models\Pembayaran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePembayaranRequest extends FormRequest
{
    /**
     * Hanya Pemilik yang boleh mengubah pembayaran.
     */
    public function authorize(): bool
    {
        return $this->user()?->isPemilik() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tanggal_bayar' => ['required', 'date', 'before_or_equal:today'],
            'nominal' => ['required', 'numeric', 'min:1', 'max:99999999'],
            // DP hangus dibuat otomatis oleh sistem saat pembatalan.
            'jenis' => ['required', Rule::in(['dp', 'pelunasan'])],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Nominal tidak boleh melebihi sisa tagihan pesanan terkait.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $pembayaran = $this->route('pembayaran');

            if (! $pembayaran instanceof Pembayaran) {
                return;
            }

            if ($pembayaran->jenis === 'dp_hangus') {
                $validator->errors()->add('nominal', 'DP hangus tercatat otomatis dan tidak dapat diubah.');

                return;
            }

            $pesanan = $pembayaran->pesanan;

            if ($pesanan->status === 'batal') {
                $validator->errors()->add('nominal', 'Pesanan sudah dibatalkan sehingga pembayaran tidak dapat diubah.');

                return;
            }

            // Nominal lama milik pembayaran ini masih dihitung pada sisa tagihan.
            $sisaTersedia = $pesanan->sisa_tagihan + (float) $pembayaran->nominal;
            $nominal = (float) $this->input('nominal');

            if ($nominal - $sisaTersedia > 0.005) {
                $validator->errors()->add(
                    'nominal',
                    'Nominal melebihi sisa tagihan (maksimal Rp '.number_format($sisaTersedia, 0, ',', '.').').'
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
