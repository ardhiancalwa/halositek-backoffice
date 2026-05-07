<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

afterEach(function () {
    DB::connection('mongodb')->table('conversations')->delete();
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
});

it('returns user conversations when user id is inside participant_ids array', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    Conversation::create([
        'name' => null,
        'is_group' => false,
        'participant_ids' => [
            (string) $userA->getKey(),
            (string) $userB->getKey(),
        ],
        'last_read_at' => [
            (string) $userA->getKey() => now()->toIso8601String(),
        ],
    ]);

    actingAs($userA, 'sanctum')
        ->getJson('/api/v1/chat/conversations')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.is_group', false);
});
