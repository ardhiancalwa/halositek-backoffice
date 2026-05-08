<?php

namespace App\DTOs\Award;

use App\Enums\AwardStatus;
use Illuminate\Foundation\Http\FormRequest;

final readonly class UpdateAwardDTO
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    private function __construct(
        public array $attributes,
    ) {
    }

    public static function fromRequest(FormRequest $request, ?string $verificationFilePath = null): self
    {
        return self::fromArray($request->validated(), $verificationFilePath);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, ?string $verificationFilePath = null): self
    {
        if (isset($data['status']) && ! $data['status'] instanceof AwardStatus) {
            $data['status'] = AwardStatus::from((string) $data['status']);
        }

        if ($verificationFilePath !== null) {
            $data['verification_file'] = $verificationFilePath;
        }

        return new self($data);
    }

    public function withoutStatus(): self
    {
        $attributes = $this->attributes;
        unset($attributes['status']);

        return new self($attributes);
    }
}
