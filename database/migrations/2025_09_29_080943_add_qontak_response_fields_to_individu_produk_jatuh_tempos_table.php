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
        Schema::table('individu_produk_jatuh_tempos', function (Blueprint $table) {
            $table->json('qontak_response_body')->nullable()->after('nomor_wa_tujuan');
            $table->string('qontak_response_id', 100)->nullable()->after('qontak_response_body');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('individu_produk_jatuh_tempos', function (Blueprint $table) {
            $table->dropColumn('qontak_response_body');
            $table->dropColumn('qontak_response_id');
        });
    }
};
