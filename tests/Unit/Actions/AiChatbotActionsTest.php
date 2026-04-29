<?php

use App\Actions\AiChatbot\GetAiChatbotPerformanceAction;
use App\Models\AiChatbotLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

afterEach(function () {
    DB::connection('mongodb')->table('ai_chatbot_logs')->delete();
    DB::connection('mongodb')->table('users')->delete();
});

it('counts ai chatbot performance metrics', function () {
    $user = User::factory()->create();

    AiChatbotLog::create([
        'user_id' => (string) $user->id,
        'prompt_preview' => 'Success prompt',
        'request_payload' => 'Success request',
        'status' => 'success',
        'generate_time_ms' => 1000,
    ]);

    AiChatbotLog::create([
        'user_id' => (string) $user->id,
        'prompt_preview' => 'Failed prompt',
        'request_payload' => 'Failed request',
        'status' => 'failed',
        'generate_time_ms' => 500,
        'error_log' => 'Timeout',
    ]);

    expect(app(GetAiChatbotPerformanceAction::class)->execute())->toBe([
        'total_generate' => 2,
        'total_success' => 1,
        'total_failed' => 1,
    ]);
});
