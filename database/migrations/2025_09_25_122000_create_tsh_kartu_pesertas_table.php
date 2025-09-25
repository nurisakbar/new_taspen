<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tsh_kartu_pesertas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_peserta', 255);
            $table->string('nomor_wa_tujuan', 32);
            $table->string('nomor_kartu', 64);
            // removed status and berlaku_sampai as requested
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tsh_kartu_pesertas');
    }
};


