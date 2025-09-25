<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pendaftaran_produks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_peserta', 255);
            $table->string('nama_produk', 255);
            $table->string('jumlah_premi', 50);
            $table->string('nomor_va', 32);
            $table->string('nomor_wa_tujuan', 32);
            $table->string('link_pengkinian_data', 2048);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_produks');
    }
};


