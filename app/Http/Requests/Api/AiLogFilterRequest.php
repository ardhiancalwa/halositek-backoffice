<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AiLogFilterRequest extends FormRequest
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
            'status' => ['nullable', 'string', 'in:success,failed'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
