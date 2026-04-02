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
        Schema::create('catalogs', function (Blueprint $table) {
            $table->uuid('architect_id')->index();
            $table->string('title');
            $table->string('style')->index();
            $table->text('description')->nullable();
            $table->json('images');
            $table->json('interior_highlights')->nullable();
            $table->string('layout_image')->nullable();
            $table->string('rooms');
            $table->bigInteger('estimated_cost');
            $table->string('area');
            $table->string('status')->default('pending')->index();
            $table->decimal('rating', 3, 1)->default(0.0);
            $table->unsignedInteger('likes_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['title' => 'text', 'description' => 'text'], 'catalogs_fulltext_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogs');
    }
};
