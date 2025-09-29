<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class KlaimPembayaran extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'nama_peserta',
        'nama_produk',
        'nomor_id_claim',
        'nomor_rekening',
        'nomor_wa_tujuan',
        'qontak_response_body',
        'qontak_response_id',
    ];

    protected $casts = [
        'qontak_response_body' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}


