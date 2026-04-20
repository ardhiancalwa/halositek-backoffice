<?php

namespace App\DTOs\Chat;

use Illuminate\Http\Request;

final readonly class SendMessageDTO
{
    public function __construct(
        public string $conversationId,
        public string $body,
        public ?string $attachment = null,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            conversationId: (string) $request->validated('conversation_id'),
            body: (string) $request->validated('body'),
            attachment: $request->validated('attachment'),
        );
    }
}
