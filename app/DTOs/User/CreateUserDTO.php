<?php

namespace App\DTOs\User;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use Illuminate\Http\Request;

final readonly class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public UserRole $role = UserRole::User,
        public AccountStatus $accountStatus = AccountStatus::Active,
        public ?string $photo_profile = null,
    ) {
    }

    public static function fromRequest(Request $request, ?string $photoProfilePath = null): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            role: UserRole::tryFrom($request->validated('role', 'user')) ?? UserRole::User,
            accountStatus: AccountStatus::tryFrom($request->validated('account_status', 'active')) ?? AccountStatus::Active,
            photo_profile: $photoProfilePath,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            role: isset($data['role'])
            ? (is_string($data['role']) ? UserRole::tryFrom($data['role']) ?? UserRole::User : $data['role'])
            : UserRole::User,
            accountStatus: isset($data['account_status'])
            ? (is_string($data['account_status']) ? AccountStatus::tryFrom($data['account_status']) ?? AccountStatus::Active : $data['account_status'])
            : AccountStatus::Active,
            photo_profile: $data['photo_profile'] ?? null,
        );
    }
}
