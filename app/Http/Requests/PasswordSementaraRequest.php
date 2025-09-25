<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PasswordSementaraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nomor_tujuan' => ['required','string','max:32'],
            'password_sementara' => ['required','string','min:4','max:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'nomor_tujuan.required' => 'nomor_tujuan wajib diisi',
            'password_sementara.required' => 'password_sementara wajib diisi',
        ];
    }
}
