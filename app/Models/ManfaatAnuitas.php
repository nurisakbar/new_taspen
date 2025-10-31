<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ManfaatAnuitas extends Model
{
    use HasUuids;

    protected $table = 'manfaat_anuitas';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nama_peserta',
        'nomor_peserta',
        'periode',
        'nilai_manfaat_bulanan',
        'saldo_nilai_tunai',
    ];
}
