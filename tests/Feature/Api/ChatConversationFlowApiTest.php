<?php

use App\Enums\UserRole;
use App\Jobs\GenerateAssistantReplyJob;
use App\Models\Consultation;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;

afterEach(function () {
    DB::connection('mongodb')->table('messages')->delete();
    DB::connection('mongodb')->table('conversations')->delete();
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
});

function createActiveConsultation(User $user, User $architect): Conversation
{
    $conversation = Conversation::create([
        'name' => 'Consultation Session',
        'is_group' => false,
        'participant_ids' => [(string) $user->getKey(), (string) $architect->getKey()],
        'last_read_at' => [
            (string) $user->getKey() => now()->toIso8601String(),
            (string) $architect->getKey() => now()->toIso8601String(),
        ],
    ]);

    $consultation = Consultation::create([
        'user_id' => (string) $user->getKey(),
        'architect_id' => (string) $architect->getKey(),
        'consultation_date' => now(),
        'duration_hours' => 2,
        'status' => 'active',
        'verification_status' => 'unverified',
        'payout_status' => 'pending',
        'conversation_id' => (string) $conversation->getKey(),
    ]);

    $conversation->consultation_id = (string) $consultation->getKey();
    $conversation->save();

    return $conversation;
}

it('supports main private conversation flow', function () {
    $sender = User::factory()->create(['role' => UserRole::User->value]);
    $receiver = User::factory()->create(['role' => UserRole::Architect->value]);

    createActiveConsultation($sender, $receiver);

    $createResponse = actingAs($sender, 'sanctum')
        ->postJson('/api/v1/chat/conversations', [
            'is_group' => false,
            'participant_ids' => [(string) $receiver->getKey()],
        ])
        ->assertCreated();

    $conversationId = $createResponse->json('data.id');

    actingAs($sender, 'sanctum')
        ->getJson('/api/v1/chat/conversations')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $conversationId);

    Http::fake(function (HttpRequest $request) {
        expect($request->url())->toEndWith('/api/v1/generate');
        expect($request['history'])->toBeArray()->toHaveCount(1);
        expect($request['history'][0]['role'])->toBe('user');
        expect($request['history'][0]['content'])->toBe('Halo, ini test message.');

        return Http::response([
            'type' => 'text',
            'content' => 'Ini jawaban AI konsultasi.',
            'prompt_used' => null,
        ], 200);
    });

    actingAs($sender, 'sanctum')
        ->postJson('/api/v1/chat/messages', [
            'conversation_id' => $conversationId,
            'body' => 'Halo, ini test message.',
        ])
        ->assertCreated()
        ->assertJsonPath('data.role', 'assistant')
        ->assertJsonPath('data.type', 'text')
        ->assertJsonPath('data.content', 'Ini jawaban AI konsultasi.');

    actingAs($receiver, 'sanctum')
        ->getJson('/api/v1/chat/conversations/' . $conversationId . '/messages')
        ->assertOk()
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('data.0.role', 'assistant')
        ->assertJsonPath('data.0.content', 'Ini jawaban AI konsultasi.')
        ->assertJsonPath('data.1.role', 'user')
        ->assertJsonPath('data.1.content', 'Halo, ini test message.');

    actingAs($receiver, 'sanctum')
        ->postJson('/api/v1/chat/conversations/' . $conversationId . '/read')
        ->assertOk()
        ->assertJsonPath('data.id', $conversationId);
});

it('does not duplicate private conversations for same participants', function () {
    $userA = User::factory()->create(['role' => UserRole::User->value]);
    $userB = User::factory()->create(['role' => UserRole::Architect->value]);

    createActiveConsultation($userA, $userB);

    actingAs($userA, 'sanctum')
        ->postJson('/api/v1/chat/conversations', [
            'is_group' => false,
            'participant_ids' => [(string) $userB->getKey()],
        ])
        ->assertCreated();

    actingAs($userA, 'sanctum')
        ->postJson('/api/v1/chat/conversations', [
            'is_group' => false,
            'participant_ids' => [(string) $userB->getKey()],
        ])
        ->assertCreated();

    expect(Conversation::query()->count())->toBe(1);
    expect(Message::query()->count())->toBe(0);
});

it('queues ai generation for image-like user message and returns processing response', function () {
    Queue::fake();
    Http::fake();

    $sender = User::factory()->create(['role' => UserRole::User->value]);
    $receiver = User::factory()->create(['role' => UserRole::Architect->value]);

    createActiveConsultation($sender, $receiver);

    $createResponse = actingAs($sender, 'sanctum')
        ->postJson('/api/v1/chat/conversations', [
            'is_group' => false,
            'participant_ids' => [(string) $receiver->getKey()],
        ])
        ->assertCreated();

    $conversationId = (string) $createResponse->json('data.id');

    actingAs($sender, 'sanctum')
        ->postJson('/api/v1/chat/messages', [
            'conversation_id' => $conversationId,
            'body' => 'Tolong gambarkan denah rumah 2 lantai minimalis',
        ])
        ->assertStatus(202)
        ->assertJsonPath('data.status', 'processing')
        ->assertJsonPath('data.conversation_id', $conversationId);

    Queue::assertPushed(GenerateAssistantReplyJob::class, function (GenerateAssistantReplyJob $job) use ($conversationId, $sender): bool {
        return $job->conversationId === $conversationId
            && $job->userId === (string) $sender->getKey()
            && str_contains(mb_strtolower($job->message), 'denah')
            && count($job->history) === 1
            && $job->history[0]['role'] === 'user';
    });

    Http::assertNothingSent();

    expect(
        Message::query()
            ->where('conversation_id', $conversationId)
            ->where('role', 'user')
            ->where('content', 'Tolong gambarkan denah rumah 2 lantai minimalis')
            ->exists()
    )->toBeTrue();

    expect(
        Message::query()
            ->where('conversation_id', $conversationId)
            ->where('role', 'assistant')
            ->count()
    )->toBe(0);
});

it('returns 503 when ai service is unavailable in main chat message endpoint', function () {
    Http::preventStrayRequests();
    Http::fake(fn () => Http::response([
        'detail' => 'Tidak dapat terhubung ke Ollama',
    ], 503));

    $sender = User::factory()->create(['role' => UserRole::User->value]);
    $receiver = User::factory()->create(['role' => UserRole::Architect->value]);

    createActiveConsultation($sender, $receiver);

    $createResponse = actingAs($sender, 'sanctum')
        ->postJson('/api/v1/chat/conversations', [
            'is_group' => false,
            'participant_ids' => [(string) $receiver->getKey()],
        ])
        ->assertCreated();

    $conversationId = (string) $createResponse->json('data.id');

    actingAs($sender, 'sanctum')
        ->postJson('/api/v1/chat/messages', [
            'conversation_id' => $conversationId,
            'body' => 'Mohon rekomendasi konsep rumah modern',
        ])
        ->assertStatus(503)
        ->assertJsonPath('success', false)
        ->assertJsonPath('status_code', 503)
        ->assertJsonPath('message', 'AI service/Ollama sedang tidak tersedia. Silakan coba lagi beberapa saat.');

    expect(
        Message::query()
            ->where('conversation_id', $conversationId)
            ->where('role', 'user')
            ->where('content', 'Mohon rekomendasi konsep rumah modern')
            ->exists()
    )->toBeTrue();

    expect(
        Message::query()
            ->where('conversation_id', $conversationId)
            ->where('role', 'assistant')
            ->count()
    )->toBe(0);
});
