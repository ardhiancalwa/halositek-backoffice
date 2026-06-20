<?php

namespace App\Http\Requests\Api\Consultation;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class InitiateConsultationPaymentRequest extends FormRequest
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
            'architect_id' => ['required', 'string', 'exists:users,_id'],
        ];
    }
}
