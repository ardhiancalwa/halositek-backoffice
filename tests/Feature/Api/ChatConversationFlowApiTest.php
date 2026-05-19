<?php

use App\Enums\UserRole;
use App\Models\Consultation;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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

    actingAs($sender, 'sanctum')
        ->postJson('/api/v1/chat/messages', [
            'conversation_id' => $conversationId,
            'body' => 'Halo, ini test message.',
        ])
        ->assertCreated()
        ->assertJsonPath('data.type', 'text')
        ->assertJsonPath('data.role', null)
        ->assertJsonPath('data.content', 'Halo, ini test message.');

    actingAs($receiver, 'sanctum')
        ->getJson('/api/v1/chat/conversations/' . $conversationId . '/messages')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.role', null)
        ->assertJsonPath('data.0.content', 'Halo, ini test message.');

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

it('stores image-like user message as regular conversation message', function () {
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
        ->assertCreated()
        ->assertJsonPath('data.role', null)
        ->assertJsonPath('data.content', 'Tolong gambarkan denah rumah 2 lantai minimalis');

    expect(
        Message::query()
            ->where('conversation_id', $conversationId)
            ->where('content', 'Tolong gambarkan denah rumah 2 lantai minimalis')
            ->exists()
    )->toBeTrue();

    expect(Message::query()->where('conversation_id', $conversationId)->count())->toBe(1);
});

it('keeps main chat message endpoint independent from ai service availability', function () {
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
        ->assertCreated()
        ->assertJsonPath('data.role', null)
        ->assertJsonPath('data.content', 'Mohon rekomendasi konsep rumah modern');

    expect(
        Message::query()
            ->where('conversation_id', $conversationId)
            ->where('content', 'Mohon rekomendasi konsep rumah modern')
            ->exists()
    )->toBeTrue();

    expect(Message::query()->where('conversation_id', $conversationId)->count())->toBe(1);
});
