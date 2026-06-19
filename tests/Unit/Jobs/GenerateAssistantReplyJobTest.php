<?php

use App\Jobs\GenerateAssistantReplyJob;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\HaloSitekAIService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

afterEach(function () {
    DB::connection('mongodb')->table('messages')->delete();
    DB::connection('mongodb')->table('conversations')->delete();
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
});

it('stores assistant text response when queued ai job is processed', function () {
    $user = User::factory()->create();
    $conversation = Conversation::create([
        'is_group' => false,
        'participant_ids' => [(string) $user->getKey()],
        'last_read_at' => [(string) $user->getKey() => now()->toIso8601String()],
    ]);

    Http::fake(fn () => Http::response([
        'type' => 'text',
        'content' => 'Ini jawaban AI async.',
        'prompt_used' => null,
    ], 200));

    $job = new GenerateAssistantReplyJob(
        (string) $user->getKey(),
        (string) $conversation->getKey(),
        'Berikan rekomendasi konsep rumah',
        [
            ['role' => 'user', 'content' => 'Berikan rekomendasi konsep rumah'],
        ],
    );

    $job->handle(app(HaloSitekAIService::class));

    expect(
        Message::query()
            ->where('conversation_id', (string) $conversation->getKey())
            ->where('role', 'assistant')
            ->where('type', 'text')
            ->where('content', 'Ini jawaban AI async.')
            ->exists()
    )->toBeTrue();
});

it('stores assistant image url response when queued ai job is processed', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $conversation = Conversation::create([
        'is_group' => false,
        'participant_ids' => [(string) $user->getKey()],
        'last_read_at' => [(string) $user->getKey() => now()->toIso8601String()],
    ]);

    Http::fake(fn () => Http::response([
        'type' => 'image',
        'content' => base64_encode('fake-png-bytes'),
        'prompt_used' => 'architectural render',
    ], 200));

    $job = new GenerateAssistantReplyJob(
        (string) $user->getKey(),
        (string) $conversation->getKey(),
        'Tolong render fasad rumah tropis',
        [
            ['role' => 'user', 'content' => 'Tolong render fasad rumah tropis'],
        ],
    );

    $job->handle(app(HaloSitekAIService::class));

    $assistantMessage = Message::query()
        ->where('conversation_id', (string) $conversation->getKey())
        ->where('role', 'assistant')
        ->first();

    expect($assistantMessage)->not->toBeNull();
    expect($assistantMessage?->type)->toBe('image');
    expect($assistantMessage?->content)->toStartWith('/storage/ai_images/');
});
