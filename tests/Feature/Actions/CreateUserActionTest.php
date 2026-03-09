<?php

use App\Actions\User\CreateUserAction;
use App\DTOs\User\CreateUserDTO;
use App\Enums\UserRole;
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

it('assigns default user role', function () {
    $dto = new CreateUserDTO(
        name: 'Default Role User',
        email: 'default@example.com',
        password: 'password123',
    );

    $action = new CreateUserAction();
    $user = $action->execute($dto);

    expect($user->role)->toBe(UserRole::User);
});

it('can assign architect role', function () {
    $dto = new CreateUserDTO(
        name: 'Architect User',
        email: 'architect@example.com',
        password: 'password123',
        role: UserRole::Architect,
    );

    $action = new CreateUserAction();
    $user = $action->execute($dto);

    expect($user->role)->toBe(UserRole::Architect);
});

it('can assign admin role', function () {
    $dto = new CreateUserDTO(
        name: 'Admin User',
        email: 'admin@example.com',
        password: 'password123',
        role: UserRole::Admin,
    );

    $action = new CreateUserAction();
    $user = $action->execute($dto);

    expect($user->role)->toBe(UserRole::Admin);
});
