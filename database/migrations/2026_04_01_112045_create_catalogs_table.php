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
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid();
            $table->string('architect_id')->index();
            $table->string('name')->index();
            $table->string('style')->index();
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            $table->json('layout_images')->nullable();
            $table->string('highlight_features')->nullable();
            $table->string('estimated_cost');
            $table->string('area')->nullable();
            $table->string('status')->default('pending')->index();
            $table->unsignedInteger('likes_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
