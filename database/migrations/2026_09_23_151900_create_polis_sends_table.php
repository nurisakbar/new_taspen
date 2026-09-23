<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('polis_sends', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pesan_id')->nullable();
            $table->string('phone_number', 32);
            $table->string('customer_name', 255);
            $table->string('polis_number', 64);
            $table->string('product_name', 255);
            $table->string('polis_url', 255);
            $table->string('address', 255);
            $table->string('address2', 255)->nullable();
            $table->string('address3', 255)->nullable();
            $table->string('name_of_the_insured', 255);
            $table->date('insurance_validity_date');
            $table->date('birth_date');
            $table->uuid('response_id')->nullable();
            $table->longText('qontak_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('polis_sends');
    }
};
