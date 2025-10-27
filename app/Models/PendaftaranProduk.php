<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PendaftaranProduk extends Model
{
    use HasUuids;

    protected $table = 'pendaftaran_produks';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nama_peserta',
        'nama_produk',
        'jumlah_premi',
        'nomor_va',
        'nomor_wa_tujuan',
        'link_pengkinian_data',
    ];
}
