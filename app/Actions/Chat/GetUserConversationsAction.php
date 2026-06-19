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
    public function execute(User $user, int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $userId = (string) $user->getKey();
        $quotedUserId = sprintf('%%"%s"%%', $userId);

        $query = Conversation::query()
            ->with('consultation')
            ->where(static function ($query) use ($userId, $quotedUserId): void {
                $query->where('participant_ids', 'all', [$userId])
                    ->orWhere('participant_ids', 'like', $quotedUserId);
            });

        if ($search !== null && $search !== '') {
            $matchingUserIds = User::query()
                ->where('name', 'like', '%' . $search . '%')
                ->get()
                ->map(fn (User $u) => (string) $u->getKey())
                ->all();

            if (empty($matchingUserIds)) {
                $query->where('id', '=', 'none');
            } else {
                $query->where(static function ($q) use ($matchingUserIds): void {
                    foreach ($matchingUserIds as $mid) {
                        $quotedMid = sprintf('%%"%s"%%', $mid);
                        $q->orWhere('participant_ids', 'all', [$mid])
                            ->orWhere('participant_ids', 'like', $quotedMid);
                    }
                });
            }
        }

        $paginator = $query->orderBy('updated_at', 'desc')
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
