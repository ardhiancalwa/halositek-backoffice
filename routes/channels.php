<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.conversation.{conversationId}', function (User $user, string $conversationId): bool {
    $conversation = Conversation::find($conversationId);

    if (! $conversation instanceof Conversation) {
        return false;
    }

    $participantIds = array_map('strval', $conversation->participant_ids ?? []);

    return in_array((string) $user->getKey(), $participantIds, true);
});
