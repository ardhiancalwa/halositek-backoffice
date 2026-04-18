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
        Schema::table('architect_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('architect_profiles', 'catalogs_file_url')) {
                $table->dropColumn('catalogs_file_url');
            }

            if (Schema::hasColumn('architect_profiles', 'awards_file_url')) {
                $table->dropColumn('awards_file_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('architect_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('architect_profiles', 'catalogs_file_url')) {
                $table->string('catalogs_file_url')->nullable();
            }

            if (! Schema::hasColumn('architect_profiles', 'awards_file_url')) {
                $table->string('awards_file_url')->nullable();
            }
        });
    }
};
