<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    private const INDEX_NAME = 'ai_chatbot_logs_created_at_ttl';

    private const SEVEN_DAYS_IN_SECONDS = 604800;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::connection('mongodb')
            ->getMongoDB()
            ->selectCollection('ai_chatbot_logs')
            ->createIndex(
                ['created_at' => 1],
                [
                    'name' => self::INDEX_NAME,
                    'expireAfterSeconds' => self::SEVEN_DAYS_IN_SECONDS,
                ]
            );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('mongodb')
            ->getMongoDB()
            ->selectCollection('ai_chatbot_logs')
            ->dropIndex(self::INDEX_NAME);
    }
};
