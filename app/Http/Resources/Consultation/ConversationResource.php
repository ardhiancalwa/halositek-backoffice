<?php

namespace App\Http\Resources\Consultation;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Conversation $conversation */
        $conversation = $this->resource;

        $authUserId = (string) $request->user()?->getKey();
        $participantIds = array_map('strval', $conversation->participant_ids ?? []);
        $lastReadAt = $conversation->last_read_at ?? [];

        return [
            'id' => (string) $conversation->getKey(),
            'name' => $conversation->name,
            'is_group' => (bool) $conversation->is_group,
            'participant_ids' => $participantIds,
            'last_read_at' => $lastReadAt[$authUserId] ?? null,
            'created_at' => $conversation->created_at?->toIso8601String(),
            'updated_at' => $conversation->updated_at?->toIso8601String(),
        ];
    }
}
