<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ManfaatAnuitasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_peserta' => ['required','string','max:255'],
            'nomor_peserta' => ['required','string','max:64'],
            'nomor_wa_tujuan' => ['required','string','max:32'],
            'periode' => ['required','string','max:64'],
            'nilai_manfaat_bulanan' => ['required','string','max:128'],
            'saldo_nilai_tunai' => ['required','string','max:128'],
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

