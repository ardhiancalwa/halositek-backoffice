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
        Schema::create('architect_profiles', function (Blueprint $table) {
            $table->uuid('user_id')->unique();
            $table->string('headline')->nullable();
            $table->text('bio')->nullable();
            $table->string('location')->nullable();
            $table->string('catalogs_file_url')->nullable();
            $table->string('awards_file_url')->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('specialization')->nullable();
            $table->decimal('rating', 3, 1)->default(0.0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('architect_profiles');
    }
};
