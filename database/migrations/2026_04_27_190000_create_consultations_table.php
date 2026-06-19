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
        Schema::create('consultations', function (Blueprint $table) {
            $table->uuid();
            $table->string('user_id')->index();
            $table->string('architect_id')->index();
            $table->dateTime('consultation_date')->index();
            $table->unsignedInteger('duration_hours')->default(1);
            $table->unsignedBigInteger('session_fee')->default(0);
            $table->longText('transcript')->nullable();
            $table->string('status')->default('completed')->index();
            $table->string('verification_status')->default('unverified')->index();
            $table->string('payout_status')->default('pending')->index();
            $table->dateTime('payout_released_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
