<?php

namespace App\DTOs\Auth;

use App\Enums\UserRole;
use Illuminate\Http\Request;

final readonly class RegisterUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public UserRole $role = UserRole::User,
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
            photo_profile: $photoProfilePath,
        );
    }
}
