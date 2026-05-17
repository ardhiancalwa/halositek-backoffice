<?php

namespace App\Actions\Chat;

use App\DTOs\Consultation\CreateConversationDTO;
use App\Models\Consultation;
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

            $existingMatches = Conversation::query()
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
                ->filter(static function (Conversation $conversation) use ($participantIds): bool {
                    $conversationParticipants = $conversation->getRawOriginal('participant_ids');
                    if (is_string($conversationParticipants)) {
                        try {
                            $decoded = json_decode($conversationParticipants, true, 512, JSON_THROW_ON_ERROR);
                            $conversationParticipants = is_array($decoded) ? $decoded : [];
                        } catch (\JsonException) {
                            $conversationParticipants = [];
                        }
                    }

                    $normalized = is_array($conversationParticipants)
                        ? array_map('strval', $conversationParticipants)
                        : [];
                    sort($normalized);

                    return $normalized === $participantIds;
                })
                ->values();

            if ($existingMatches->isNotEmpty()) {
                // Prioritaskan percakapan yang memiliki sesi konsultasi aktif
                $preferred = $existingMatches->first(static function (Conversation $conversation): bool {
                    if (empty($conversation->consultation_id)) {
                        return false;
                    }
                    $consultation = Consultation::find($conversation->consultation_id);

                    return $consultation instanceof Consultation && $consultation->isSessionActive();
                });

                if ($preferred instanceof Conversation) {
                    return $preferred;
                }

                // Jika tidak ada yang aktif, pilih percakapan dengan konsultasi terbaru
                $latest = null;
                $latestDate = null;
                foreach ($existingMatches as $conv) {
                    if (empty($conv->consultation_id)) {
                        continue;
                    }
                    $consultation = Consultation::find($conv->consultation_id);
                    if ($consultation instanceof Consultation && $consultation->consultation_date) {
                        $date = $consultation->consultation_date;
                        if ($latestDate === null || $date->gt($latestDate)) {
                            $latestDate = $date;
                            $latest = $conv;
                        }
                    }
                }

                return $latest ?? $existingMatches->first();
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
