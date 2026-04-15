<?php

namespace App\Http\Requests\Api\V1\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProjectRequest extends FormRequest
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
            'style' => ['sometimes', 'required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'images' => ['sometimes', 'array', 'min:1', 'max:10'],
            'images.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'estimated_cost' => ['sometimes', 'required', 'string', 'max:255'],
            'layout_images' => ['sometimes', 'array', 'min:1', 'max:10'],
            'layout_images.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'highlight_features' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:100'],
        ];
    }
}
