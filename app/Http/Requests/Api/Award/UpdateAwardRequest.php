<?php

namespace App\Http\Requests\Api\Award;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateAwardRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user !== null && ($user->isArchitect() || $user->isAdmin());
    }

    /**
     * @return array<string, array<int, string|\Closure>>
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'required', 'string', 'in:pending,approved,declined'],
            'name' => ['sometimes', 'required', 'string', 'max:255', $this->maxWordsRule(12, 'name')],
            'project_name' => ['sometimes', 'required', 'string', 'max:255', $this->maxWordsRule(12, 'project name')],
            'role' => ['sometimes', 'required', 'string', 'max:255'],
            'award_date' => ['sometimes', 'required', 'date'],
            'description' => ['nullable', 'string', $this->maxWordsRule(30, 'description')],
            'verification_file' => ['sometimes', 'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    private function maxWordsRule(int $maxWords, string $attributeLabel): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($maxWords, $attributeLabel): void {
            if (! is_string($value)) {
                return;
            }

            $wordCount = str_word_count(trim($value));

            if ($wordCount > $maxWords) {
                $fail("The {$attributeLabel} may not be greater than {$maxWords} words.");
            }
        };
    }
}
