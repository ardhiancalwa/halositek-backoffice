<?php

namespace App\Http\Resources\Consultation;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Message $message */
        $message = $this->resource;
        $authUserId = (string) $request->user()?->getKey();
        $role = is_string($message->role) ? $message->role : null;
        $isMine = $role !== Message::ROLE_ASSISTANT
            && $authUserId !== ''
            && $authUserId === (string) $message->user_id;

        return [
            'id' => (string) $message->getKey(),
            'conversation_id' => (string) $message->conversation_id,
            'user_id' => (string) $message->user_id,
            'role' => $role,
            'type' => is_string($message->type) ? $message->type : null,
            'content' => is_string($message->content) ? $message->content : null,
            'body' => $message->body,
            'attachment' => $message->attachment,
            'attachment_url' => $message->attachment ? Storage::url($message->attachment) : null,
            'read_at' => $message->read_at?->toIso8601String(),
            'is_mine' => $isMine,
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
