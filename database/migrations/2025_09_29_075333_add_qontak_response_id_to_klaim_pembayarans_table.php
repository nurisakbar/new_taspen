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
        Schema::table('klaim_pembayarans', function (Blueprint $table) {
            $table->string('qontak_response_id', 100)->nullable()->after('qontak_response_body');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('klaim_pembayarans', function (Blueprint $table) {
            $table->dropColumn('qontak_response_id');
        });
    }
};
