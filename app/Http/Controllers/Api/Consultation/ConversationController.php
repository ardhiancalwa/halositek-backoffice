<?php

namespace App\Http\Controllers\Api\Consultation;

use App\Actions\Chat\CreateConversationAction;
use App\Actions\Chat\GetUserConversationsAction;
use App\DTOs\Consultation\CreateConversationDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Consultation\CreateConversationRequest;
use App\Http\Resources\Consultation\ChatListResource;
use App\Http\Resources\Consultation\ConversationResource;
use App\Http\Responses\ApiResponse;
use App\Models\Conversation;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class ConversationController extends Controller
{
    /**
     * @OA\Get(
     *   path="/chat/conversations",
     *   tags={"Chat"},
     *   security={{"BearerAuth":{}}},
     *   summary="List user conversations",
     *   description="Mengambil daftar percakapan milik user yang sedang login.",
     *
     *   @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=50, example=15)),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Daftar percakapan berhasil diambil",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "Daftar percakapan berhasil diambil.",
     *         "data": {
     *           {
     *             "id": "01J2CHATCONVERSATION001",
     *             "name": null,
     *             "is_group": false,
     *             "participant_ids": {"01J2USERA", "01J2USERB"},
     *             "last_read_at": "2026-04-19T10:00:00+00:00",
     *             "unread_count": 2,
     *             "last_message": {
     *               "id": "01J2MESSAGE001",
     *               "conversation_id": "01J2CHATCONVERSATION001",
     *               "user_id": "01J2USERB",
     *               "body": "Halo, ada update project terbaru.",
     *               "attachment": null,
     *               "read_at": null,
     *               "is_mine": false,
     *               "created_at": "2026-04-19T09:59:00+00:00",
     *               "updated_at": "2026-04-19T09:59:00+00:00"
     *             },
     *             "updated_at": "2026-04-19T09:59:00+00:00"
     *           }
     *         },
     *         "meta": {"current_page": 1, "last_page": 1, "per_page": 15, "total": 1},
     *         "links": {
     *           "first_page_url": "http://localhost:8000/api/v1/chat/conversations?page=1",
     *           "last_page_url": "http://localhost:8000/api/v1/chat/conversations?page=1",
     *           "next_page_url": null,
     *           "prev_page_url": null
     *         }
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function index(Request $request, GetUserConversationsAction $action): JsonResponse
    {
        $perPage = min(50, (int) $request->input('per_page', 15));
        $conversations = $action->execute($request->user(), $perPage);

        $conversations->setCollection(
            ChatListResource::collection($conversations->getCollection())->collection
        );

        return ApiResponse::paginated($conversations, 'Daftar percakapan berhasil diambil.');
    }

    /**
     * @OA\Post(
     *   path="/chat/conversations",
     *   tags={"Chat"},
     *   security={{"BearerAuth":{}}},
     *   summary="Create conversation",
     *   description="Membuat percakapan baru (default private chat). Jika private chat sudah ada, data existing dapat dikembalikan.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       required={"participant_ids"},
     *
     *       @OA\Property(property="name", type="string", nullable=true, example=null),
     *       @OA\Property(property="is_group", type="boolean", example=false),
     *       @OA\Property(property="participant_ids", type="array", @OA\Items(type="string"), example={"01J2USERB"})
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=201,
     *     description="Percakapan berhasil dibuat",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 201,
     *         "message": "Percakapan berhasil dibuat.",
     *         "data": {
     *           "id": "01J2CHATCONVERSATION001",
     *           "name": null,
     *           "is_group": false,
     *           "participant_ids": {"01J2USERA", "01J2USERB"},
     *           "last_read_at": "2026-04-19T10:00:00+00:00",
     *           "created_at": "2026-04-19T10:00:00+00:00",
     *           "updated_at": "2026-04-19T10:00:00+00:00"
     *         }
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function store(CreateConversationRequest $request, CreateConversationAction $action): JsonResponse
    {
        $conversation = $action->execute(
            CreateConversationDTO::fromRequest($request),
            $request->user(),
        );

        return ApiResponse::created(
            (new ConversationResource($conversation))->resolve($request),
            'Percakapan berhasil dibuat.',
        );
    }

    /**
     * @OA\Get(
     *   path="/chat/conversations/{conversationId}",
     *   tags={"Chat"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get conversation detail",
     *   description="Mengambil detail percakapan berdasarkan ID conversation.",
     *
     *   @OA\Parameter(name="conversationId", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Detail percakapan berhasil diambil",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "Detail percakapan berhasil diambil.",
     *         "data": {
     *           "id": "01J2CHATCONVERSATION001",
     *           "name": null,
     *           "is_group": false,
     *           "participant_ids": {"01J2USERA", "01J2USERB"},
     *           "last_read_at": "2026-04-19T10:00:00+00:00",
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
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function show(Request $request, string $conversationId): JsonResponse
    {
        $conversation = Conversation::findOrFail($conversationId);
        $participantIds = array_map('strval', $conversation->participant_ids ?? []);

        if (! in_array((string) $request->user()->getKey(), $participantIds, true)) {
            throw new AuthorizationException('Anda tidak memiliki akses ke percakapan ini.');
        }

        return ApiResponse::success(
            (new ConversationResource($conversation))->resolve($request),
            'Detail percakapan berhasil diambil.',
        );
    }
}
