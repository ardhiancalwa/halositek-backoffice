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
        Schema::create('consultation_reports', function (Blueprint $table) {
            $table->uuid();
            $table->string('consultation_id')->index();
            $table->string('requester_id')->index();
            $table->string('opposing_party_id')->index();
            $table->string('requester_role')->index();
            $table->text('reason');
            $table->string('action_status')->default('new')->index();
            $table->string('actioned_by')->nullable()->index();
            $table->dateTime('actioned_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_reports');
    }
};
