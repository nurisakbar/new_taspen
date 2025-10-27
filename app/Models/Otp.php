<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Otp extends Model
{
    use HasFactory;

    protected $table = 'otp';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'nomor_tujuan',
        'kode_otp',
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
