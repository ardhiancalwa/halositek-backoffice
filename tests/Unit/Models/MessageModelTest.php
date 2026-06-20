<?php

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;

afterEach(function () {
    DB::connection('mongodb')->table('messages')->delete();
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
});

it('defaults message type to text and mirrors body into content when content is missing', function () {
    $user = User::factory()->create();

    $message = Message::create([
        'user_id' => (string) $user->getKey(),
        'body' => 'Halo ini pesan biasa.',
    ]);

    expect($message->type)->toBe('text');
    expect($message->content)->toBe('Halo ini pesan biasa.');
});

it('only allows message role user or assistant', function () {
    $user = User::factory()->create();

    expect(function () use ($user): void {
        Message::create([
            'user_id' => (string) $user->getKey(),
            'role' => 'admin',
            'content' => 'Tidak valid',
            'type' => 'text',
        ]);
    })->toThrow(InvalidArgumentException::class);
});
