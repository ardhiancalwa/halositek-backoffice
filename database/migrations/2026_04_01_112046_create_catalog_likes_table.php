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
        Schema::create('catalog_likes', function (Blueprint $table) {
            $table->uuid('catalog_id')->index();
            $table->uuid('user_id')->index();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['catalog_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_likes');
    }
};
