<?php

namespace App\Actions\User;

use App\DTOs\User\CreateUserDTO;
use App\Enums\UserRole;
use App\Models\ArchitectProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class CreateUserAction
{
    public function execute(CreateUserDTO $dto): User
    {
        $user = User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
            'role' => $dto->role->value,
            'account_status' => $dto->accountStatus->value,
            'photo_profile' => $dto->photo_profile,
        ]);

        if ($dto->role === UserRole::Architect) {
            ArchitectProfile::create([
                'user_id' => (string) $user->id,
                'status' => 'approved',
                'headline' => $dto->headline,
            ]);
        }

        return $user;
    }
}
