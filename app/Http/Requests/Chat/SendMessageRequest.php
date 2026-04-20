<?php

namespace App\Http\Requests\Chat;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SendMessageRequest extends FormRequest
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
            'conversation_id' => ['required', 'string', 'exists:conversations,_id'],
            'body' => ['required_without:attachment', 'string', 'max:4000'],
            'attachment' => ['nullable', 'string', 'max:2048'],
        ];
    }
}
