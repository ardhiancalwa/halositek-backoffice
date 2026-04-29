<?php

namespace App\Http\Requests\Api\Consultation;

use Illuminate\Foundation\Http\FormRequest;

class ConsultationReportFilterRequest extends FormRequest
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
            'role' => ['nullable', 'string', 'in:user,architect'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
