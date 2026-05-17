<?php

namespace App\Http\Resources\Consultation;

use App\Enums\UserRole;
use App\Http\Resources\User\ArchitectProfileResource;
use App\Http\Resources\User\UserResource;
use App\Models\Consultation;
use App\Models\Conversation;
use App\Models\User;
use Carbon\Carbon;
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

        $participants = $conversation->relationLoaded('participants')
            ? $conversation->participants
            : User::query()
                ->whereIn('id', $participantIds)
                ->with('architectProfile')
                ->get();

        $userParticipant = $participants->first(fn ($u) => $u->role === UserRole::User) ?? $participants->first(fn ($u) => $u->role !== UserRole::Architect);
        $architectParticipant = $participants->first(fn ($u) => $u->role === UserRole::Architect);

        $lastChatTime = $conversation->relationLoaded('lastMessage') && $conversation->lastMessage !== null
            ? $conversation->lastMessage->created_at
            : $conversation->updated_at;

        $lastChatFormatted = $this->formatLastChatTime($lastChatTime);

        return [
            'id' => (string) $conversation->getKey(),
            'name' => $conversation->name,
            'is_group' => (bool) $conversation->is_group,
            'participant_ids' => $participantIds,
            'user' => $userParticipant ? new UserResource($userParticipant) : null,
            'architect' => $architectParticipant ? new ArchitectProfileResource($architectParticipant) : null,
            'last_read_at' => $lastReadAt[$authUserId] ?? null,
            'consultation_id' => $conversation->consultation_id,
            'consultation_session' => $consultationSession,
            'can_send_message' => $consultationSession === null ? true : (bool) $consultationSession['is_active'],
            'unread_count' => (int) ($conversation->getAttribute('unread_count') ?? 0),
            'last_message' => $conversation->relationLoaded('lastMessage') && $conversation->lastMessage !== null
                ? (new MessageResource($conversation->lastMessage))->resolve($request)
                : null,
            'last_chat_formatted' => $lastChatFormatted,
            'updated_at' => $conversation->updated_at?->toIso8601String(),
        ];
    }

    private function formatLastChatTime(?Carbon $dateTime): ?string
    {
        if (! $dateTime) {
            return null;
        }

        $now = now();
        $diffInHours = $dateTime->diffInHours($now);

        if ($diffInHours < 24) {
            return $dateTime->format('H:i');
        }

        if ($dateTime->isYesterday()) {
            return 'Kemarin';
        }

        $diffInDays = $dateTime->diffInDays($now);
        if ($diffInDays < 7) {
            $days = [
                0 => 'Minggu',
                1 => 'Senin',
                2 => 'Selasa',
                3 => 'Rabu',
                4 => 'Kamis',
                5 => 'Jumat',
                6 => 'Sabtu',
            ];

            return $days[$dateTime->dayOfWeek];
        }

        return $dateTime->format('d/m/Y');
    }
}
