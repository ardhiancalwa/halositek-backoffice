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
        Schema::create('architect_wishlists', function (Blueprint $table) {
            $table->uuid('user_id')->index();
            $table->uuid('architect_id')->index();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'architect_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('architect_wishlists');
    }
};
