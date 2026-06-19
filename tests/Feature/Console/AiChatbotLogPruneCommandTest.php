<?php

use App\Models\AiChatbotLog;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

afterEach(function () {
    DB::connection('mongodb')->table('ai_chatbot_logs')->delete();
    DB::connection('mongodb')->table('users')->delete();
});

it('deletes ai chatbot logs older than seven days', function () {
    $user = User::factory()->create();

    $oldLog = AiChatbotLog::create([
        'user_id' => (string) $user->getKey(),
        'prompt_preview' => 'Old prompt.',
        'request_payload' => 'Old prompt.',
        'status' => 'success',
        'generate_time_ms' => 100,
    ]);
    forceLogTimestamp($oldLog, now()->subDays(8));

    $freshLog = AiChatbotLog::create([
        'user_id' => (string) $user->getKey(),
        'prompt_preview' => 'Fresh prompt.',
        'request_payload' => 'Fresh prompt.',
        'status' => 'success',
        'generate_time_ms' => 100,
    ]);
    forceLogTimestamp($freshLog, now()->subDays(6));

    Artisan::call('ai-chatbot:prune-logs');

    expect(AiChatbotLog::find((string) $oldLog->getKey()))->toBeNull();
    expect(AiChatbotLog::find((string) $freshLog->getKey()))->not->toBeNull();
});

it('exposes ai chatbot logs as a prunable model', function () {
    $user = User::factory()->create();

    $oldLog = AiChatbotLog::create([
        'user_id' => (string) $user->getKey(),
        'prompt_preview' => 'Old prompt.',
        'request_payload' => 'Old prompt.',
        'status' => 'success',
        'generate_time_ms' => 100,
    ]);
    forceLogTimestamp($oldLog, now()->subDays(8));

    $freshLog = AiChatbotLog::create([
        'user_id' => (string) $user->getKey(),
        'prompt_preview' => 'Fresh prompt.',
        'request_payload' => 'Fresh prompt.',
        'status' => 'success',
        'generate_time_ms' => 100,
    ]);
    forceLogTimestamp($freshLog, now()->subDays(6));

    expect((new AiChatbotLog())->prunable()->count())->toBe(1);
});

function forceLogTimestamp(AiChatbotLog $log, DateTimeInterface $timestamp): void
{
    $log->timestamps = false;
    $log->created_at = $timestamp;
    $log->updated_at = $timestamp;
    $log->save();
}
