<?php

namespace App\Actions\Chat;

use App\DTOs\Chat\CreateConversationDTO;
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
                ->where('participant_ids', $participantIds[0])
                ->where('participant_ids', $participantIds[1])
                ->get()
                ->first(static fn (Conversation $conversation): bool => count($conversation->participant_ids ?? []) === 2);

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
