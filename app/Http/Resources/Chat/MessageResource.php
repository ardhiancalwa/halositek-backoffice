<?php

namespace App\Http\Resources\Chat;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Message $message */
        $message = $this->resource;

        return [
            'id' => (string) $message->getKey(),
            'conversation_id' => (string) $message->conversation_id,
            'user_id' => (string) $message->user_id,
            'body' => $message->body,
            'attachment' => $message->attachment,
            'read_at' => $message->read_at?->toIso8601String(),
            'is_mine' => (string) $request->user()?->getKey() === (string) $message->user_id,
            'sender' => $this->whenLoaded('sender', function () use ($message): ?array {
                $sender = $message->sender;
                if ($sender === null) {
                    return null;
                }

                return [
                    'id' => (string) $sender->getKey(),
                    'name' => $sender->name,
                    'email' => $sender->email,
                ];
            }),
            'created_at' => $message->created_at?->toIso8601String(),
            'updated_at' => $message->updated_at?->toIso8601String(),
        ];
    }
}
