<?php

namespace App\Http\Requests\Chat;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'is_group' => ['sometimes', 'boolean'],
            'participant_ids' => ['required', 'array', 'min:1'],
            'participant_ids.*' => ['required', 'string', 'exists:users,_id'],
        ];
    }
}
