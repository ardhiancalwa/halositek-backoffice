<?php

namespace App\Http\Requests\Api\Consultation;

use Illuminate\Foundation\Http\FormRequest;

class ConsultationReportActionRequest extends FormRequest
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
            'action' => ['required', 'string', 'in:approved,declined'],
        ];
    }
}
