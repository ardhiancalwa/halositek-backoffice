<?php

namespace App\DTOs\Faq;

use Illuminate\Foundation\Http\FormRequest;

final readonly class CreateFaqDTO
{
    public function __construct(
        public string $question,
        public string $answer,
        public bool $isActive = true,
    ) {
    }

    public static function fromRequest(FormRequest $request): self
    {
        return self::fromArray($request->validated());
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            question: (string) $data['question'],
            answer: (string) $data['answer'],
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }
}
