<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('individu_produk_jatuh_tempos', function (Blueprint $table) {
            $table->id();
            $table->string('nama_peserta', 255);
            $table->string('nomor_polis', 64);
            $table->string('nomor_va', 64);
            $table->string('produk_asuransi', 128);
            $table->unsignedBigInteger('premi_per_bulan');
            $table->date('tanggal_tagihan');
            $table->string('bulan_tagihan', 32);
            $table->string('nomor_wa_tujuan', 32);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('individu_produk_jatuh_tempos');
    }
};


