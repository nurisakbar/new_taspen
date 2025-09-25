<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class IndividuProdukJatuhTempoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_peserta' => ['required','string','max:255'],
            'nomor_polis' => ['required','string','max:64'],
            'nomor_va' => ['required','string','max:64'],
            'produk_asuransi' => ['required','string','max:128'],
            'premi_per_bulan' => ['required','numeric','min:0'],
            'periode_tagihan' => ['required','string','max:32'],
            'nomor_wa_tujuan' => ['required','string','max:32'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors' => $validator->errors(),
        ], 422));
    }
}



