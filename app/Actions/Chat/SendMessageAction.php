<?php

namespace App\Actions\Chat;

use App\DTOs\Consultation\SendMessageDTO;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Auth\Access\AuthorizationException;

final class SendMessageAction
{
    public function execute(SendMessageDTO $dto, User $sender): Message
    {
        $conversation = Conversation::findOrFail($dto->conversationId);
        $senderId = (string) $sender->getKey();

        $participantIds = array_map('strval', $conversation->participant_ids ?? []);
        if (! in_array($senderId, $participantIds, true)) {
            throw new AuthorizationException('Anda tidak memiliki akses ke percakapan ini.');
        }

        $message = Message::create([
            'conversation_id' => (string) $conversation->getKey(),
            'user_id' => $senderId,
            'body' => $dto->body,
            'attachment' => $dto->attachment,
            'read_at' => null,
        ]);

        $lastReadAt = $conversation->last_read_at ?? [];
        $lastReadAt[$senderId] = now()->toIso8601String();

        $conversation->last_read_at = $lastReadAt;
        $conversation->updated_at = now();
        $conversation->save();

        $message->setRelation('sender', $sender);
        broadcast(new MessageSent($message))->toOthers();

        foreach ($participantIds as $participantId) {
            if ($participantId === $senderId) {
                continue;
            }

            $recipient = User::find($participantId);
            if (! $recipient instanceof User) {
                continue;
            }

            $recipient->notify(new NewMessageNotification($message));
        }

        return $message;
    }
}
