<?php

namespace App\Actions\Chat;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

final class MarkMessageAsReadAction
{
    public function execute(string $conversationId, User $user): Conversation
    {
        $conversation = Conversation::findOrFail($conversationId);
        $userId = (string) $user->getKey();

        $participantIds = array_map('strval', $conversation->participant_ids ?? []);
        if (! in_array($userId, $participantIds, true)) {
            throw new AuthorizationException('Anda tidak memiliki akses ke percakapan ini.');
        }

        Message::query()
            ->where('conversation_id', (string) $conversation->getKey())
            ->where('user_id', '!=', $userId)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        $lastReadAt = $conversation->last_read_at ?? [];
        $lastReadAt[$userId] = now()->toIso8601String();

        $conversation->last_read_at = $lastReadAt;
        $conversation->save();

        return $conversation;
    }
}
