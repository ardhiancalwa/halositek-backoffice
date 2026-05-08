<?php

namespace App\Actions\AiChatbot;

use App\Models\AiChatbotLog;

final class GetAiChatbotPerformanceAction
{
    /**
     * @return array{total_generate: int, total_success: int, total_failed: int}
     */
    public function execute(): array
    {
        return [
            'total_generate' => AiChatbotLog::query()->count(),
            'total_success' => AiChatbotLog::query()->where('status', 'success')->count(),
            'total_failed' => AiChatbotLog::query()->where('status', 'failed')->count(),
        ];
    }
}
