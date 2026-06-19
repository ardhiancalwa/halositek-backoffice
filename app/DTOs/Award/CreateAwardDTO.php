<?php

namespace App\DTOs\Award;

use App\Enums\AwardStatus;
use Illuminate\Foundation\Http\FormRequest;

final readonly class CreateAwardDTO
{
    public function __construct(
        public string $architectId,
        public string $name,
        public string $projectName,
        public string $role,
        public string $awardDate,
        public ?string $description = null,
        public ?string $verificationFile = null,
        public AwardStatus $status = AwardStatus::Pending,
    ) {
    }

    public static function fromRequest(FormRequest $request, string $architectId, ?string $verificationFilePath = null): self
    {
        return self::fromArray($request->validated(), $architectId, $verificationFilePath);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, string $architectId, ?string $verificationFilePath = null): self
    {
        return new self(
            architectId: $architectId,
            name: (string) $data['name'],
            projectName: (string) $data['project_name'],
            role: (string) $data['role'],
            awardDate: (string) $data['award_date'],
            description: isset($data['description']) ? (string) $data['description'] : null,
            verificationFile: $verificationFilePath,
        );
    }
}
