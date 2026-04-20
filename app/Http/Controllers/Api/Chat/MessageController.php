<?php

namespace App\Http\Controllers\Api\Chat;

use App\Actions\Chat\MarkMessageAsReadAction;
use App\Actions\Chat\SendMessageAction;
use App\DTOs\Chat\SendMessageDTO;
use App\Events\TypingIndicator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Resources\Chat\ConversationResource;
use App\Http\Resources\Chat\MessageResource;
use App\Http\Responses\ApiResponse;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class MessageController extends Controller
{
    /**
     * @OA\Get(
     *   path="/chat/conversations/{conversationId}/messages",
     *   tags={"Chat"},
     *   security={{"BearerAuth":{}}},
     *   summary="List messages",
     *   description="Mengambil daftar pesan pada conversation tertentu.",
     *
     *   @OA\Parameter(name="conversationId", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100, example=20)),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Daftar pesan berhasil diambil",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "Daftar pesan berhasil diambil.",
     *         "data": {
     *           {
     *             "id": "01J2MESSAGE001",
     *             "conversation_id": "01J2CHATCONVERSATION001",
     *             "user_id": "01J2USERA",
     *             "body": "Halo!",
     *             "attachment": null,
     *             "read_at": null,
     *             "is_mine": true,
     *             "sender": {
     *               "id": "01J2USERA",
     *               "name": "Budi",
     *               "email": "budi@example.com"
     *             },
     *             "created_at": "2026-04-19T10:00:00+00:00",
     *             "updated_at": "2026-04-19T10:00:00+00:00"
     *           }
     *         },
     *         "meta": {"current_page": 1, "last_page": 1, "per_page": 20, "total": 1},
     *         "links": {
     *           "first_page_url": "http://localhost:8000/api/v1/chat/conversations/01J2CHATCONVERSATION001/messages?page=1",
     *           "last_page_url": "http://localhost:8000/api/v1/chat/conversations/01J2CHATCONVERSATION001/messages?page=1",
     *           "next_page_url": null,
     *           "prev_page_url": null
     *         }
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function index(Request $request, string $conversationId): JsonResponse
    {
        $conversation = Conversation::findOrFail($conversationId);
        $participantIds = array_map('strval', $conversation->participant_ids ?? []);

        if (! in_array((string) $request->user()->getKey(), $participantIds, true)) {
            throw new AuthorizationException('Anda tidak memiliki akses ke percakapan ini.');
        }

        $perPage = min(100, (int) $request->input('per_page', 20));

        $messages = Message::query()
            ->with('sender')
            ->where('conversation_id', (string) $conversation->getKey())
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $messages->setCollection(
            MessageResource::collection($messages->getCollection())->collection
        );

        return ApiResponse::paginated($messages, 'Daftar pesan berhasil diambil.');
    }

    /**
     * @OA\Post(
     *   path="/chat/messages",
     *   tags={"Chat"},
     *   security={{"BearerAuth":{}}},
     *   summary="Send message",
     *   description="Mengirim pesan teks ke conversation dan men-trigger broadcast real-time.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       required={"conversation_id", "body"},
     *
     *       @OA\Property(property="conversation_id", type="string", example="01J2CHATCONVERSATION001"),
     *       @OA\Property(property="body", type="string", example="Halo, kabar kamu gimana?"),
     *       @OA\Property(property="attachment", type="string", nullable=true, example=null)
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=201,
     *     description="Pesan berhasil dikirim",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 201,
     *         "message": "Pesan berhasil dikirim.",
     *         "data": {
     *           "id": "01J2MESSAGE001",
     *           "conversation_id": "01J2CHATCONVERSATION001",
     *           "user_id": "01J2USERA",
     *           "body": "Halo, kabar kamu gimana?",
     *           "attachment": null,
     *           "read_at": null,
     *           "is_mine": true,
     *           "sender": {
     *             "id": "01J2USERA",
     *             "name": "Budi",
     *             "email": "budi@example.com"
     *           },
     *           "created_at": "2026-04-19T10:00:00+00:00",
     *           "updated_at": "2026-04-19T10:00:00+00:00"
     *         }
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function store(SendMessageRequest $request, SendMessageAction $action): JsonResponse
    {
        $message = $action->execute(
            SendMessageDTO::fromRequest($request),
            $request->user(),
        );

        return ApiResponse::created(
            (new MessageResource($message->loadMissing('sender')))->resolve($request),
            'Pesan berhasil dikirim.',
        );
    }

    /**
     * @OA\Post(
     *   path="/chat/conversations/{conversationId}/read",
     *   tags={"Chat"},
     *   security={{"BearerAuth":{}}},
     *   summary="Mark conversation as read",
     *   description="Menandai seluruh pesan lawan bicara pada conversation sebagai sudah dibaca dan update last_read_at user.",
     *
     *   @OA\Parameter(name="conversationId", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Pesan berhasil ditandai sudah dibaca",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "Pesan berhasil ditandai sudah dibaca.",
     *         "data": {
     *           "id": "01J2CHATCONVERSATION001",
     *           "name": null,
     *           "is_group": false,
     *           "participant_ids": {"01J2USERA", "01J2USERB"},
     *           "last_read_at": "2026-04-19T10:05:00+00:00",
     *           "created_at": "2026-04-19T10:00:00+00:00",
     *           "updated_at": "2026-04-19T10:05:00+00:00"
     *         }
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function markAsRead(Request $request, string $conversationId, MarkMessageAsReadAction $action): JsonResponse
    {
        $conversation = $action->execute($conversationId, $request->user());

        return ApiResponse::success(
            (new ConversationResource($conversation))->resolve($request),
            'Pesan berhasil ditandai sudah dibaca.',
        );
    }

    /**
     * @OA\Post(
     *   path="/chat/conversations/{conversationId}/typing",
     *   tags={"Chat"},
     *   security={{"BearerAuth":{}}},
     *   summary="Send typing indicator",
     *   description="Mengirim status typing indicator ke private channel conversation.",
     *
     *   @OA\Parameter(name="conversationId", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\RequestBody(
     *     required=false,
     *
     *     @OA\JsonContent(
     *
     *       @OA\Property(property="is_typing", type="boolean", example=true)
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Status mengetik berhasil dikirim",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "Status mengetik berhasil dikirim."
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function typing(Request $request, string $conversationId): JsonResponse
    {
        $conversation = Conversation::findOrFail($conversationId);
        $participantIds = array_map('strval', $conversation->participant_ids ?? []);
        $userId = (string) $request->user()->getKey();

        if (! in_array($userId, $participantIds, true)) {
            throw new AuthorizationException('Anda tidak memiliki akses ke percakapan ini.');
        }

        $isTyping = (bool) $request->boolean('is_typing', true);

        broadcast(new TypingIndicator((string) $conversation->getKey(), $userId, $isTyping))->toOthers();

        return ApiResponse::success(message: 'Status mengetik berhasil dikirim.');
    }
}
