<?php

use App\Models\AiChatbotLog;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;

afterEach(function () {
    Cache::flush();
    DB::connection('mongodb')->table('ai_chatbot_logs')->delete();
    DB::connection('mongodb')->table('messages')->delete();
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
});

it('validates required message on ai chat endpoint', function () {
    $user = User::factory()->create();

    actingAs($user, 'sanctum')
        ->postJson('/api/v1/chat/ai/messages', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['message']);
});

it('sends last 10 history messages to ai service and stores user plus assistant response', function () {
    $user = User::factory()->create();
    $userId = (string) $user->getKey();

    foreach (range(1, 12) as $index) {
        Message::create([
            'user_id' => $userId,
            'role' => $index % 2 === 0 ? 'assistant' : 'user',
            'type' => 'text',
            'content' => 'msg-' . $index,
            'body' => 'msg-' . $index,
            'attachment' => null,
            'read_at' => null,
        ]);
    }

    Http::fake(function (HttpRequest $request) use ($userId) {
        expect($request->url())->toEndWith('/api/v1/generate');
        expect($request['user_id'])->toBe($userId);
        expect($request['message'])->toBe('Buatkan konsep rumah tropis');
        expect($request['generation_id'])->toBeString();
        expect($request['history'])->toBeArray();
        expect(count($request['history']))->toBeLessThanOrEqual(10);
        foreach ($request['history'] as $historyItem) {
            expect($historyItem['role'])->toBeIn(['user', 'assistant']);
            expect($historyItem['content'])->toBeString();
        }

        return Http::response([
            'type' => 'text',
            'content' => 'Ini adalah jawaban AI.',
            'prompt_used' => null,
        ], 200);
    });

    actingAs($user, 'sanctum')
        ->postJson('/api/v1/chat/ai/messages', [
            'message' => 'Buatkan konsep rumah tropis',
        ])
        ->assertOk()
        ->assertJsonPath('type', 'text')
        ->assertJsonPath('content', 'Ini adalah jawaban AI.');

    expect(Message::query()->where('user_id', $userId)->count())->toBe(14);

    expect(
        Message::query()
            ->where('user_id', $userId)
            ->where('role', 'user')
            ->where('content', 'Buatkan konsep rumah tropis')
            ->exists()
    )->toBeTrue();

    expect(
        Message::query()
            ->where('user_id', $userId)
            ->where('role', 'assistant')
            ->where('type', 'text')
            ->where('content', 'Ini adalah jawaban AI.')
            ->exists()
    )->toBeTrue();

    $log = AiChatbotLog::query()->where('user_id', $userId)->first();
    expect($log)->not->toBeNull();
    expect($log->status)->toBe('success');
    expect($log->prompt_preview)->toBe('Buatkan konsep rumah tropis');
    expect($log->request_payload)->toBe('Buatkan konsep rumah tropis');
    expect($log->result_type)->toBe('text');
    expect($log->generated_text)->toBe('Ini adalah jawaban AI.');
});

it('returns 503 when ai service is unavailable', function () {
    $user = User::factory()->create();

    Http::fake(fn () => Http::response([
        'detail' => 'Tidak dapat terhubung ke Ollama',
    ], 503));

    actingAs($user, 'sanctum')
        ->postJson('/api/v1/chat/ai/messages', [
            'message' => 'Buatkan denah rumah',
        ])
        ->assertStatus(503)
        ->assertJsonPath('success', false)
        ->assertJsonPath('status_code', 503)
        ->assertJsonPath('message', 'AI service/Ollama sedang tidak tersedia. Silakan coba lagi beberapa saat.');

    expect(AiChatbotLog::query()->where('user_id', (string) $user->getKey())->where('status', 'failed')->exists())
        ->toBeTrue();
});

it('returns 503 when ai service tunnel is offline', function () {
    $user = User::factory()->create();

    Http::fake(fn () => Http::response('ERR_NGROK_3200 endpoint is offline', 404));

    actingAs($user, 'sanctum')
        ->postJson('/api/v1/chat/ai/messages', [
            'message' => 'Buatkan konsep rumah',
        ])
        ->assertStatus(503)
        ->assertJsonPath('success', false)
        ->assertJsonPath('status_code', 503)
        ->assertJsonPath('message', 'AI service/Ollama sedang tidak tersedia. Silakan coba lagi beberapa saat.');
});

it('returns 422 when ai service rejects invalid request', function () {
    $user = User::factory()->create();

    Http::fake(fn () => Http::response([
        'detail' => [
            [
                'loc' => ['body', 'user_id'],
                'msg' => 'Field required',
            ],
        ],
    ], 422));

    actingAs($user, 'sanctum')
        ->postJson('/api/v1/chat/ai/messages', [
            'message' => 'Halo',
        ])
        ->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('status_code', 422)
        ->assertJsonPath('message', 'Permintaan tidak dapat diproses oleh AI service. Mohon periksa pesan Anda.');
});

it('returns 500 for unexpected ai service errors without exposing stack trace', function () {
    $user = User::factory()->create();

    Http::fake(fn () => Http::response([
        'detail' => 'Unexpected downstream failure',
    ], 500));

    actingAs($user, 'sanctum')
        ->postJson('/api/v1/chat/ai/messages', [
            'message' => 'Halo AI',
        ])
        ->assertStatus(500)
        ->assertJsonPath('success', false)
        ->assertJsonPath('status_code', 500)
        ->assertJsonPath('message', 'Terjadi kesalahan internal saat memproses permintaan AI.')
        ->assertJsonMissingPath('trace')
        ->assertJsonMissingPath('data.trace');

    expect(AiChatbotLog::query()->where('user_id', (string) $user->getKey())->where('status', 'failed')->exists())
        ->toBeTrue();
});

it('stops an active ai generation and suppresses returned result', function () {
    $user = User::factory()->create();
    $userId = (string) $user->getKey();

    Http::fake(function (HttpRequest $request) use ($userId) {
        if (str_ends_with($request->url(), '/api/v1/generate')) {
            Cache::put(
                'ai-generation-cancelled:' . $userId . ':' . $request['generation_id'],
                true,
                now()->addMinutes(10)
            );

            return Http::response([
                'type' => 'text',
                'content' => 'Jawaban yang tidak boleh disimpan.',
            ], 200);
        }

        return Http::response([], 404);
    });

    actingAs($user, 'sanctum')
        ->postJson('/api/v1/chat/ai/messages', [
            'message' => 'Generate lama',
        ])
        ->assertStatus(409)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Generate AI dihentikan.');

    expect(Message::query()->where('user_id', $userId)->exists())->toBeFalse();
    expect(
        AiChatbotLog::query()
            ->where('user_id', $userId)
            ->where('status', 'failed')
            ->where('error_log', 'Generate AI dihentikan oleh user.')
            ->exists()
    )->toBeTrue();
});

it('can request active ai generation stop', function () {
    $user = User::factory()->create();
    $userId = (string) $user->getKey();

    Cache::put('ai-generation-active:' . $userId, 'generation-1', now()->addMinutes(10));

    Http::fake(function (HttpRequest $request) use ($userId) {
        expect($request->url())->toEndWith('/api/v1/generate/stop');
        expect($request['user_id'])->toBe($userId);
        expect($request['generation_id'])->toBe('generation-1');

        return Http::response(['stopped' => true], 200);
    });

    actingAs($user, 'sanctum')
        ->postJson('/api/v1/chat/ai/stop')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Generate AI berhasil dihentikan.')
        ->assertJsonPath('data.stopped', true);

    expect(Cache::has('ai-generation-cancelled:' . $userId . ':generation-1'))->toBeTrue();
});
