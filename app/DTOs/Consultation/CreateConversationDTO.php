<?php

namespace App\DTOs\Consultation;

use App\Http\Requests\Api\Consultation\CreateConversationRequest;

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

    public static function fromRequest(CreateConversationRequest $request): self
    {
        /** @var array{participant_ids?: mixed, name?: mixed, is_group?: mixed} $validated */
        $validated = $request->validated();
        $participantIds = $validated['participant_ids'] ?? [];
        $name = $validated['name'] ?? null;
        $isGroup = $validated['is_group'] ?? false;

        return new self(
            participantIds: array_values(array_unique(array_map('strval', $participantIds))),
            name: is_string($name) ? $name : null,
            isGroup: (bool) $isGroup,
        );
    }
}
