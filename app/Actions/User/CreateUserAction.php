<?php

namespace App\Actions\User;

use App\DTOs\User\CreateUserDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class CreateUserAction
{
    public function execute(CreateUserDTO $dto): User
    {
        return User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
            'role' => $dto->role->value,
            'account_status' => $dto->accountStatus->value,
        ]);
    }
}
