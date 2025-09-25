<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Handle cases where id is BIGINT AUTO_INCREMENT PRIMARY KEY
        // 1) Remove AUTO_INCREMENT by modifying column to BIGINT without AI
        // 2) Drop primary key
        // 3) Change type to CHAR(36)
        // 4) Re-add primary key
        try {
            DB::statement('ALTER TABLE pesan MODIFY id BIGINT UNSIGNED NOT NULL');
        } catch (\Throwable $e) {
            // Ignore if already not BIGINT or no AUTO_INCREMENT
        }

        try {
            DB::statement('ALTER TABLE pesan DROP PRIMARY KEY');
        } catch (\Throwable $e) {
            // Ignore if no primary key to drop
        }

        DB::statement('ALTER TABLE pesan MODIFY id CHAR(36) NOT NULL');
        DB::statement('ALTER TABLE pesan ADD PRIMARY KEY (id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE pesan DROP PRIMARY KEY');
        } catch (\Throwable $e) {
            // ignore
        }

        DB::statement('ALTER TABLE pesan MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE pesan ADD PRIMARY KEY (id)');
    }
};
