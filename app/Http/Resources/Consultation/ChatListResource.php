<?php

namespace App\Http\Resources\Consultation;

use App\Models\Consultation;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatListResource extends JsonResource
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
        $consultation = $conversation->relationLoaded('consultation')
            ? $conversation->consultation
            : ($conversation->consultation_id ? Consultation::find($conversation->consultation_id) : null);

        $consultationSession = null;
        if ($consultation instanceof Consultation) {
            $isActive = $consultation->isSessionActive();
            $expiresAt = $consultation->expiresAt();
            $remainingDuration = $consultation->remainingDuration();
            $remainingSeconds = $consultation->remainingSeconds();

            $consultationSession = [
                'id' => (string) $consultation->getKey(),
                'status' => (string) ($consultation->status ?? 'active'),
                'started_at' => $consultation->consultation_date?->toIso8601String(),
                'expires_at' => $expiresAt?->toIso8601String(),
                'remaining_seconds' => $remainingSeconds,
                'remaining_duration' => $remainingDuration,
                'is_active' => $isActive,
            ];
        }

        return [
            'id' => (string) $conversation->getKey(),
            'name' => $conversation->name,
            'is_group' => (bool) $conversation->is_group,
            'participant_ids' => $participantIds,
            'last_read_at' => $lastReadAt[$authUserId] ?? null,
            'consultation_id' => $conversation->consultation_id,
            'consultation_session' => $consultationSession,
            'can_send_message' => $consultationSession === null ? true : (bool) $consultationSession['is_active'],
            'unread_count' => (int) ($conversation->getAttribute('unread_count') ?? 0),
            'last_message' => $conversation->relationLoaded('lastMessage') && $conversation->lastMessage !== null
                ? (new MessageResource($conversation->lastMessage))->resolve($request)
                : null,
            'updated_at' => $conversation->updated_at?->toIso8601String(),
        ];
    }
}
