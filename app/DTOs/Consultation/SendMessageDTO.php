<?php

namespace App\DTOs\Consultation;

use App\Http\Requests\Api\Consultation\SendMessageRequest;

final readonly class SendMessageDTO
{
    public function __construct(
        public string $conversationId,
        public ?string $body = null,
        public ?string $attachment = null,
    ) {
    }

    public static function fromRequest(SendMessageRequest $request, ?string $attachmentPath = null): self
    {
        $validated = $request->validated();

        return new self(
            conversationId: (string) ($validated['conversation_id'] ?? ''),
            body: isset($validated['body']) ? (string) $validated['body'] : null,
            attachment: $attachmentPath ?? (isset($validated['attachment']) && is_string($validated['attachment']) ? $validated['attachment'] : null),
        );
    }
}
