<?php

use App\Actions\User\CreateUserAction;
use App\DTOs\User\CreateUserDTO;
use App\Models\User;
use Illuminate\Support\Facades\DB;

afterEach(function () {
    DB::connection('mongodb')->table('users')->delete();
});

it('creates a user from DTO', function () {
    $dto = new CreateUserDTO(
        name: 'Test User',
        email: 'test@example.com',
        password: 'password123',
    );

    $action = new CreateUserAction();
    $user = $action->execute($dto);

    expect($user)
        ->toBeInstanceOf(User::class)
        ->name->toBe('Test User')
        ->email->toBe('test@example.com');

    expect(User::where('email', 'test@example.com')->exists())->toBeTrue();
});

it('hashes the password when creating user', function () {
    $dto = new CreateUserDTO(
        name: 'Test User',
        email: 'hash@example.com',
        password: 'plaintext',
    );

    $action = new CreateUserAction();
    $user = $action->execute($dto);

    expect($user->password)->not->toBe('plaintext');
    expect(\Illuminate\Support\Facades\Hash::check('plaintext', $user->password))->toBeTrue();
});
