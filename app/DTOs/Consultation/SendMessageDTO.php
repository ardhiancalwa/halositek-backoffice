<?php

namespace App\DTOs\Consultation;

use App\Http\Requests\Api\Consultation\SendMessageRequest;

final readonly class SendMessageDTO
{
    public function __construct(
        public string $conversationId,
        public string $body,
        public ?string $attachment = null,
    ) {
    }

    public static function fromRequest(SendMessageRequest $request): self
    {
        /** @var array{conversation_id?: mixed, body?: mixed, attachment?: mixed} $validated */
        $validated = $request->validated();

        return new self(
            conversationId: (string) ($validated['conversation_id'] ?? ''),
            body: (string) ($validated['body'] ?? ''),
            attachment: isset($validated['attachment']) && is_string($validated['attachment'])
                ? $validated['attachment']
                : null,
        );
    }
}
