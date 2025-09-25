<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TshKartuPeserta extends Model
{
    use HasUuids;

    protected $table = 'tsh_kartu_pesertas';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nama_peserta',
        'nomor_wa_tujuan',
        'nomor_kartu',
    ];
}


