<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PolisSend extends Model
{
    use HasUuids;

    protected $table = 'polis_sends';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'phone_number',
        'customer_name',
        'polis_number',
        'product_name',
        'polis_url',
        'address',
        'address2',
        'address3',
        'name_of_the_insured',
        'insurance_validity_date',
        'birth_date',
        'pesan_id',
        'response_id',
        'qontak_response',
    ];

    protected $casts = [
        'qontak_response' => 'array',
    ];
}
