<?php

namespace App\Http\Requests\Api\V1\Faq;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateFaqRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question' => 'sometimes|string',
            'answer' => 'sometimes|string',
            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny(['question', 'answer', 'is_active'])) {
                $validator->errors()->add('payload', 'Minimal satu field harus dikirim untuk update FAQ.');
            }
        });
    }
}
