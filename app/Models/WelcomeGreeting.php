<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WelcomeGreeting extends Model
{
    use HasUuids;

    protected $table = 'welcome_greetings';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nama_peserta',
        'nomor_wa_tujuan',
    ];
}
