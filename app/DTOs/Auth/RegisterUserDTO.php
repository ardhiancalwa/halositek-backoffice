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
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            role: UserRole::tryFrom($request->validated('role', 'user')) ?? UserRole::User,
        );
    }
}
