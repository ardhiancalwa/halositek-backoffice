<?php

namespace App\Actions\Chat;

use App\DTOs\Consultation\CreateConversationDTO;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class CreateConversationAction
{
    public function execute(CreateConversationDTO $dto, User $authUser): Conversation
    {
        $authUserId = (string) $authUser->getKey();

        $participantIds = array_values(array_unique(array_merge(
            [$authUserId],
            array_map('strval', $dto->participantIds),
        )));

        if (! $dto->isGroup && count($participantIds) !== 2) {
            throw ValidationException::withMessages([
                'participant_ids' => ['Private chat harus berisi tepat 2 partisipan.'],
            ]);
        }

        if (! $dto->isGroup) {
            sort($participantIds);

            $existing = Conversation::query()
                ->where('is_group', false)
                ->where(static function ($query) use ($participantIds): void {
                    $first = sprintf('%%"%s"%%', $participantIds[0]);
                    $second = sprintf('%%"%s"%%', $participantIds[1]);

                    $query->where('participant_ids', 'all', $participantIds)
                        ->orWhere(static function ($q) use ($first, $second): void {
                            $q->where('participant_ids', 'like', $first)
                                ->where('participant_ids', 'like', $second);
                        });
                })
                ->get()
                ->first(static function (Conversation $conversation) use ($participantIds): bool {
                    $conversationParticipants = $conversation->participant_ids;
                    if (is_string($conversationParticipants)) {
                        try {
                            $decoded = json_decode($conversationParticipants, true, 512, JSON_THROW_ON_ERROR);
                            $conversationParticipants = is_array($decoded) ? $decoded : [];
                        } catch (\JsonException) {
                            $conversationParticipants = [];
                        }
                    }

                    $normalized = array_values(array_map('strval', is_array($conversationParticipants) ? $conversationParticipants : []));
                    sort($normalized);

                    return $normalized === $participantIds;
                });

            if ($existing instanceof Conversation) {
                return $existing;
            }
        }

        return Conversation::create([
            'name' => $dto->name,
            'is_group' => $dto->isGroup,
            'participant_ids' => $participantIds,
            'last_read_at' => [
                $authUserId => now()->toIso8601String(),
            ],
        ]);
    }
}
