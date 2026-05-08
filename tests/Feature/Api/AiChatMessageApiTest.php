<?php

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;

afterEach(function () {
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
});
