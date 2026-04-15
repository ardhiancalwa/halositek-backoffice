<?php

namespace App\Http\Requests\Api\V1\Award;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateAwardRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        $role = is_object($user?->role) ? $user->role->value : $user?->role;

        return $user !== null && in_array($role, ['architect', 'admin'], true);
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'required', 'string', 'in:pending,approved,declined'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'project_name' => ['sometimes', 'required', 'string', 'max:255'],
            'role' => ['sometimes', 'required', 'string', 'max:255'],
            'award_date' => ['sometimes', 'required', 'date'],
            'description' => ['nullable', 'string'],
            'verification_file' => ['sometimes', 'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
