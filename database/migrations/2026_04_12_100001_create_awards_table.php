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
        Schema::create('awards', function (Blueprint $table) {
            $table->uuid();
            $table->string('architect_id')->index();
            $table->string('name');
            $table->string('project_name');
            $table->string('role');
            $table->date('award_date');
            $table->text('description')->nullable();
            $table->string('verification_file')->nullable();
            $table->string('status')->default('pending')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('awards');
    }
};
