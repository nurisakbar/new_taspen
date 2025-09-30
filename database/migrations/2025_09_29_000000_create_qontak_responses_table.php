<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qontak_responses', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint')->nullable();
            $table->string('to_number')->nullable();
            $table->string('to_name')->nullable();
            $table->string('message_template_id')->nullable();
            $table->string('channel_integration_id')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_body')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qontak_responses');
    }
};




