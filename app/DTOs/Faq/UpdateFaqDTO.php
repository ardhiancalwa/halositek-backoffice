<?php

namespace App\DTOs\Faq;

use Illuminate\Foundation\Http\FormRequest;

final readonly class UpdateFaqDTO
{
    /**
     * @param  array<string, string|bool>  $attributes
     */
    private function __construct(
        public array $attributes,
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
        $attributes = [];

        foreach (['question', 'answer'] as $key) {
            if (array_key_exists($key, $data)) {
                $attributes[$key] = (string) $data[$key];
            }
        }

        if (array_key_exists('is_active', $data)) {
            $attributes['is_active'] = (bool) $data['is_active'];
        }

        return new self($attributes);
    }
}
