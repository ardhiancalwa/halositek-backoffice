<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_chatbot_logs', function (Blueprint $table) {
            $table->uuid();
            $table->string('user_id')->nullable()->index();
            $table->text('prompt_preview');
            $table->longText('request_payload')->nullable();
            $table->string('status')->index();
            $table->unsignedInteger('generate_time_ms')->default(0);
            $table->string('result_type')->nullable();
            $table->longText('generated_text')->nullable();
            $table->string('generated_image_url')->nullable();
            $table->longText('error_log')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_chatbot_logs');
    }
};
