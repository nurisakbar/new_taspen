<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class WelcomeGreetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_peserta' => ['required','string','max:255'],
            'nomor_wa_tujuan' => ['required','string','max:32'],
            'sandi' => ['required','string','max:255'],
            'nomor_polis' => ['required','string','max:64'],
            'polis_url' => ['required','string','max:255'],
            'nama_produk' => ['required','string','max:255'],
            'tanggal_mulai_asuransi' => ['required','date'],
            'alamat' => ['required','string','max:255'],
            'tanggal_lahir' => ['required','date']
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
