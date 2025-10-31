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
        Schema::create('manfaat_anuitas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_peserta', 255);
            $table->string('nomor_peserta', 64);
            $table->string('periode', 64);
            $table->string('nilai_manfaat_bulanan', 128);
            $table->string('saldo_nilai_tunai', 128);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manfaat_anuitas');
    }
};
