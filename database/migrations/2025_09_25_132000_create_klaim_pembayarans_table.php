<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klaim_pembayarans', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('nama_peserta', 255);
            $table->string('nama_produk', 128);
            $table->string('nomor_id_claim', 64);
            $table->string('nomor_rekening', 64);
            $table->string('nomor_wa_tujuan', 32);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klaim_pembayarans');
    }
};


