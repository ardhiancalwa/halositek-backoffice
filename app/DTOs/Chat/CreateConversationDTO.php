<?php

namespace App\DTOs\Chat;

use Illuminate\Http\Request;

final readonly class CreateConversationDTO
{
    /**
     * @param  list<string>  $participantIds
     */
    public function __construct(
        public array $participantIds,
        public ?string $name = null,
        public bool $isGroup = false,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $participantIds = $request->validated('participant_ids', []);

        return new self(
            participantIds: array_values(array_unique(array_map('strval', $participantIds))),
            name: $request->validated('name'),
            isGroup: (bool) $request->validated('is_group', false),
        );
    }
}
