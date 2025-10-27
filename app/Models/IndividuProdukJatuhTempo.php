<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class IndividuProdukJatuhTempo extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nama_peserta',
        'nomor_polis',
        'nomor_va',
        'produk_asuransi',
        'premi_per_bulan',
        'periode_tagihan',
        'jenis_jatuh_tempo',
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
