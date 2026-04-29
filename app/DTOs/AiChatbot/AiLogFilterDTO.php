<?php

namespace App\DTOs\AiChatbot;

use Illuminate\Foundation\Http\FormRequest;

final readonly class AiLogFilterDTO
{
    public function __construct(
        public ?string $status = null,
        public int $perPage = 15,
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
            status: isset($data['status']) && $data['status'] !== '' ? (string) $data['status'] : null,
            perPage: (int) ($data['per_page'] ?? 15),
        );
    }
}
