<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('welcome_greetings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_peserta', 255);
            $table->string('nomor_wa_tujuan', 32);
            // message field removed as per requirement
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('welcome_greetings');
    }
};


