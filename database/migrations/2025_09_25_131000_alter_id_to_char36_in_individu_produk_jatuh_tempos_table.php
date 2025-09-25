<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement('ALTER TABLE individu_produk_jatuh_tempos MODIFY id BIGINT UNSIGNED NOT NULL');
        } catch (\Throwable $e) {
            // ignore if already not BIGINT or no AUTO_INCREMENT
        }

        try {
            DB::statement('ALTER TABLE individu_produk_jatuh_tempos DROP PRIMARY KEY');
        } catch (\Throwable $e) {
            // ignore if already dropped
        }

        DB::statement('ALTER TABLE individu_produk_jatuh_tempos MODIFY id CHAR(36) NOT NULL');
        DB::statement('ALTER TABLE individu_produk_jatuh_tempos ADD PRIMARY KEY (id)');
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE individu_produk_jatuh_tempos DROP PRIMARY KEY');
        } catch (\Throwable $e) {
            // ignore
        }

        DB::statement('ALTER TABLE individu_produk_jatuh_tempos MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE individu_produk_jatuh_tempos ADD PRIMARY KEY (id)');
    }
};


