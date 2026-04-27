<?php

namespace App\Http\Requests\Api\Award;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreAwardRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user !== null && $user->isArchitect();
    }

    /**
     * @return array<string, array<int, string|\Closure>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', $this->maxWordsRule(12, 'name')],
            'project_name' => ['required', 'string', 'max:255', $this->maxWordsRule(12, 'project name')],
            'role' => ['required', 'string', 'max:255'],
            'award_date' => ['required', 'date'],
            'description' => ['nullable', 'string', $this->maxWordsRule(30, 'description')],
            'verification_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
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
