<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PayrollReleaseRequest extends FormRequest
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
            'consultation_ids' => ['nullable', 'array', 'min:1'],
            'consultation_ids.*' => ['required', 'string'],
        ];
    }
}
