<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE polis_sends MODIFY birth_date VARCHAR(32) NULL');
        DB::statement('ALTER TABLE polis_sends MODIFY insurance_validity_date VARCHAR(32) NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE polis_sends MODIFY insurance_validity_date DATE NOT NULL');
        DB::statement('ALTER TABLE polis_sends MODIFY birth_date DATE NOT NULL');
    }
};
