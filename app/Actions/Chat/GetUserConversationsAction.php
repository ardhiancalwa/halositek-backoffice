<?php

namespace App\Actions\Chat;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

final class GetUserConversationsAction
{
    /**
     * @return LengthAwarePaginator<int, Conversation>
     */
    public function execute(User $user, int $perPage = 15): LengthAwarePaginator
    {
        $userId = (string) $user->getKey();

        $paginator = Conversation::query()
            ->where('participant_ids', $userId)
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage);

        $collection = $paginator->getCollection()->map(function (Conversation $conversation) use ($userId): Conversation {
            $lastMessage = Message::query()
                ->where('conversation_id', (string) $conversation->getKey())
                ->orderBy('created_at', 'desc')
                ->first();

            $unreadCount = Message::query()
                ->where('conversation_id', (string) $conversation->getKey())
                ->where('user_id', '!=', $userId)
                ->whereNull('read_at')
                ->count();

            $conversation->setAttribute('unread_count', $unreadCount);
            $conversation->setRelation('lastMessage', $lastMessage);

            return $conversation;
        });

        $paginator->setCollection($collection);

        return $paginator;
    }
}
