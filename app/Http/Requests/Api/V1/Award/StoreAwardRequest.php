<?php

namespace App\Http\Requests\Api\V1\Award;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreAwardRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        $role = is_object($user?->role) ? $user->role->value : $user?->role;

        return $user !== null && $role === 'architect';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'project_name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'award_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'verification_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
