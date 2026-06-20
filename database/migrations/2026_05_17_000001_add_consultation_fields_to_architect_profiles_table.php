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
            $table->integer('consultation_fee')->default(0);
            $table->integer('consultation_duration')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('architect_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('architect_profiles', 'consultation_fee')) {
                $table->dropColumn('consultation_fee');
            }
            if (Schema::hasColumn('architect_profiles', 'consultation_duration')) {
                $table->dropColumn('consultation_duration');
            }
        });
    }
};
