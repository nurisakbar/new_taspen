<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('individu_produk_jatuh_tempos', function (Blueprint $table) {
            if (!Schema::hasColumn('individu_produk_jatuh_tempos', 'periode_tagihan')) {
                $table->string('periode_tagihan', 32)->after('premi_per_bulan');
            }
            if (!Schema::hasColumn('individu_produk_jatuh_tempos', 'jenis_jatuh_tempo')) {
                $table->string('jenis_jatuh_tempo', 32)->after('periode_tagihan');
            }
            if (Schema::hasColumn('individu_produk_jatuh_tempos', 'tanggal_tagihan')) {
                $table->dropColumn('tanggal_tagihan');
            }
            if (Schema::hasColumn('individu_produk_jatuh_tempos', 'bulan_tagihan')) {
                $table->dropColumn('bulan_tagihan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('individu_produk_jatuh_tempos', function (Blueprint $table) {
            if (!Schema::hasColumn('individu_produk_jatuh_tempos', 'tanggal_tagihan')) {
                $table->date('tanggal_tagihan')->after('premi_per_bulan');
            }
            if (!Schema::hasColumn('individu_produk_jatuh_tempos', 'bulan_tagihan')) {
                $table->string('bulan_tagihan', 32)->after('tanggal_tagihan');
            }
            if (Schema::hasColumn('individu_produk_jatuh_tempos', 'jenis_jatuh_tempo')) {
                $table->dropColumn('jenis_jatuh_tempo');
            }
            if (Schema::hasColumn('individu_produk_jatuh_tempos', 'periode_tagihan')) {
                $table->dropColumn('periode_tagihan');
            }
        });
    }
};


