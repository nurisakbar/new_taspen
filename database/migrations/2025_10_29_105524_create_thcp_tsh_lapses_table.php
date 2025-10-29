<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('thcp_tsh_lapses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_peserta', 255);
            $table->string('nomor_polis', 64);
            $table->string('produk_asuransi', 128);
            $table->string('nomor_wa_tujuan', 32);
            $table->json('qontak_response_body')->nullable();
            $table->string('qontak_response_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thcp_tsh_lapses');
    }
};
