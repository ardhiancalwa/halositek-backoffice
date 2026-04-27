<?php

use App\Models\AiChatbotLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

afterEach(function () {
    DB::connection('mongodb')->table('ai_chatbot_logs')->delete();
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
});

it('returns performance metrics and filtered logs', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    AiChatbotLog::create([
        'user_id' => (string) $user->getKey(),
        'prompt_preview' => 'Buat konsep desain modern.',
        'request_payload' => 'prompt lengkap success',
        'status' => 'success',
        'generate_time_ms' => 2100,
        'result_type' => 'text',
        'generated_text' => 'Hasil generate text.',
    ]);

    AiChatbotLog::create([
        'user_id' => (string) $user->getKey(),
        'prompt_preview' => 'Buat render detail.',
        'request_payload' => 'prompt lengkap failed',
        'status' => 'failed',
        'generate_time_ms' => 980,
        'error_log' => 'Model timeout.',
    ]);

    actingAs($admin, 'sanctum')
        ->getJson('/api/v1/ai-chatbot/performance')
        ->assertOk()
        ->assertJsonPath('data.total_generate', 2)
        ->assertJsonPath('data.total_success', 1)
        ->assertJsonPath('data.total_failed', 1);

    actingAs($admin, 'sanctum')
        ->getJson('/api/v1/ai-chatbot/logs?status=failed')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.status', 'failed');
});

it('returns detail payload for success and failed log', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $successLog = AiChatbotLog::create([
        'user_id' => (string) $user->getKey(),
        'prompt_preview' => 'Prompt success.',
        'request_payload' => 'original request success',
        'status' => 'success',
        'generate_time_ms' => 1200,
        'result_type' => 'image',
        'generated_image_url' => 'https://example.com/image.jpg',
    ]);

    $failedLog = AiChatbotLog::create([
        'user_id' => (string) $user->getKey(),
        'prompt_preview' => 'Prompt failed.',
        'request_payload' => 'original request failed',
        'status' => 'failed',
        'generate_time_ms' => 450,
        'error_log' => 'Internal AI failure.',
    ]);

    actingAs($admin, 'sanctum')
        ->getJson('/api/v1/ai-chatbot/logs/' . $successLog->getKey())
        ->assertOk()
        ->assertJsonPath('data.status', 'success')
        ->assertJsonPath('data.original_request', 'original request success')
        ->assertJsonPath('data.generate_result.type', 'image')
        ->assertJsonPath('data.generate_result.image_url', 'https://example.com/image.jpg');

    actingAs($admin, 'sanctum')
        ->getJson('/api/v1/ai-chatbot/logs/' . $failedLog->getKey())
        ->assertOk()
        ->assertJsonPath('data.status', 'failed')
        ->assertJsonPath('data.original_request', 'original request failed')
        ->assertJsonPath('data.system_error_log', 'Internal AI failure.');
});
