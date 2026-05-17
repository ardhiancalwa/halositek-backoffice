<?php

namespace App\Actions\Auth;

use App\DTOs\Auth\RegisterUserDTO;
use App\Enums\UserRole;
use App\Models\ArchitectProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class RegisterUserAction
{
    public function execute(RegisterUserDTO $dto): User
    {
        $user = User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
            'role' => $dto->role->value,
            'account_status' => 'active',
            'photo_profile' => $dto->photo_profile,
        ]);

        if ($dto->role === UserRole::Architect) {
            ArchitectProfile::create([
                'user_id' => (string) $user->id,
                'status' => 'approved',
            ]);
        }

        return $user;
    }
}
