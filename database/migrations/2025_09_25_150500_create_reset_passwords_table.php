<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reset_passwords', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_tujuan', 32);
            $table->string('password_sementara', 64);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reset_passwords');
    }
};
