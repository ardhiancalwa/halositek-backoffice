<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('messages')) {
            return;
        }

        // Backfill existing chat documents so missing/null/empty type defaults to text.
        DB::connection('mongodb')
            ->table('messages')
            ->whereNull('type')
            ->update(['type' => 'text']);

        DB::connection('mongodb')
            ->table('messages')
            ->where('type', '')
            ->update(['type' => 'text']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op to avoid destructive rollback on existing chat data.
    }
};
