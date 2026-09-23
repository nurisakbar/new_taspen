<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number' => 'required',
            'customer_name' => 'required',
            'polis_number' => 'required',
            'product_name' => 'required',
            'polis_url' => 'required',
            'address' => 'required',
            'name_of_the_insured' => 'required',
            'insurance_validity_date' => 'required',
        ];
    }
}
