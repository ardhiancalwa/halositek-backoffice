<?php

use App\Enums\UserRole;
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

it('supports main private conversation flow', function () {
    $sender = User::factory()->create(['role' => UserRole::User->value]);
    $receiver = User::factory()->create(['role' => UserRole::Architect->value]);

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
        ->assertJsonPath('data.body', 'Halo, ini test message.');

    actingAs($receiver, 'sanctum')
        ->getJson('/api/v1/chat/conversations/' . $conversationId . '/messages')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.body', 'Halo, ini test message.');

    actingAs($receiver, 'sanctum')
        ->postJson('/api/v1/chat/conversations/' . $conversationId . '/read')
        ->assertOk()
        ->assertJsonPath('data.id', $conversationId);
});

it('does not duplicate private conversations for same participants', function () {
    $userA = User::factory()->create(['role' => UserRole::User->value]);
    $userB = User::factory()->create(['role' => UserRole::Architect->value]);

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
