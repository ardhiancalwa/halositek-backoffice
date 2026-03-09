<?php

namespace App\Actions\Auth;

use App\DTOs\Auth\RegisterUserDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class RegisterUserAction
{
    public function execute(RegisterUserDTO $dto): User
    {
        return User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
            'role' => $dto->role->value,
        ]);
    }
}
