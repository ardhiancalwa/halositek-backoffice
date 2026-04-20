<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'role' => ['required', 'string', 'in:' . implode(',', array_map(static fn (UserRole $role): string => $role->value, UserRole::cases()))],
        ];
    }
}
