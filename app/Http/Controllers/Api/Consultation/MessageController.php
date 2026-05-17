<?php

namespace App\Http\Controllers\Api\Consultation;

use App\Actions\Chat\MarkMessageAsReadAction;
use App\Actions\Chat\SendMessageAction;
use App\DTOs\Consultation\SendMessageDTO;
use App\Enums\ApiStatus;
use App\Events\TypingIndicator;
use App\Exceptions\HaloSitekAIException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Consultation\SendMessageRequest;
use App\Http\Resources\Consultation\ConversationResource;
use App\Http\Resources\Consultation\MessageResource;
use App\Http\Responses\ApiResponse;
use App\Jobs\GenerateAssistantReplyJob;
use App\Models\Consultation;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\HaloSitekAIService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;
use Throwable;

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
     *   description="Mengirim pesan teks ke conversation. Untuk role user, sistem memproses AI reply (sync untuk teks, async/queue untuk request gambar).",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *
     *       @OA\Schema(
     *         type="object",
     *         required={"conversation_id"},
     *
     *         @OA\Property(property="conversation_id", type="string", example="01J2CHATCONVERSATION001"),
     *         @OA\Property(property="body", type="string", nullable=true, example="Halo, kabar kamu gimana?"),
     *         @OA\Property(property="attachment", type="string", format="binary", nullable=true)
     *       )
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=201,
     *     description="Pesan berhasil diproses dan AI reply sinkron dikembalikan",
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
     *           "role": "assistant",
     *           "type": "text",
     *           "content": "Ini jawaban AI konsultasi.",
     *           "body": "Ini jawaban AI konsultasi.",
     *           "attachment": null,
     *           "read_at": null,
     *           "is_mine": false,
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
     *   @OA\Response(
     *     response=202,
     *     description="Permintaan AI sedang diproses melalui queue",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 202,
     *         "message": "Permintaan AI sedang diproses.",
     *         "data": {
     *           "status": "processing",
     *           "conversation_id": "01J2CHATCONVERSATION001",
     *           "user_message_id": "01J2MESSAGE001"
     *         }
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=503, ref="#/components/responses/ServiceUnavailableError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function store(SendMessageRequest $request, SendMessageAction $action, HaloSitekAIService $ai): JsonResponse
    {
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('chat/attachments', 'public');
        }

        $message = $action->execute(
            SendMessageDTO::fromRequest($request, $attachmentPath),
            $request->user(),
        );

        /** @var User $user */
        $user = $request->user();
        if (! $user->isUser()) {
            return ApiResponse::created(
                (new MessageResource($message->loadMissing('sender')))->resolve($request),
                'Pesan berhasil dikirim.',
            );
        }

        $userId = (string) ($user->getAttribute('_id') ?? $user->getKey());
        $message->role = Message::ROLE_USER;
        $message->type = $message->attachment ? Message::TYPE_IMAGE : Message::TYPE_TEXT;
        $message->content = is_string($message->body) ? $message->body : '';
        $message->save();

        $history = Message::query()
            ->where('conversation_id', (string) $message->conversation_id)
            ->whereIn('role', [Message::ROLE_USER, Message::ROLE_ASSISTANT])
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->reverse()
            ->map(function (Message $historyMessage): array {
                $content = $historyMessage->content;
                if (! is_string($content) || $content === '') {
                    $content = is_string($historyMessage->body) ? $historyMessage->body : '';
                }

                return [
                    'role' => (string) $historyMessage->role,
                    'content' => $content,
                ];
            })
            ->filter(fn (array $historyItem): bool => in_array($historyItem['role'], [Message::ROLE_USER, Message::ROLE_ASSISTANT], true) && $historyItem['content'] !== '')
            ->values()
            ->all();

        if ($this->shouldQueueAiRequest((string) ($message->body ?? ''))) {
            try {
                GenerateAssistantReplyJob::dispatch(
                    $userId,
                    (string) $message->conversation_id,
                    (string) ($message->body ?? ''),
                    $history,
                );
            } catch (Throwable $exception) {
                Log::error('Failed to dispatch queued AI chat job.', [
                    'user_id' => $userId,
                    'conversation_id' => (string) $message->conversation_id,
                    'error' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                ]);

                return ApiResponse::error(
                    'Terjadi kesalahan internal saat memproses permintaan AI.',
                    ApiStatus::SERVER_ERROR
                );
            }

            return ApiResponse::success(
                [
                    'status' => 'processing',
                    'conversation_id' => (string) $message->conversation_id,
                    'user_message_id' => (string) $message->getKey(),
                ],
                'Permintaan AI sedang diproses.',
                ApiStatus::ACCEPTED
            );
        }

        try {
            $result = $ai->generate(
                userId: $userId,
                message: (string) ($message->body ?? ''),
                history: $history,
            );

            $assistantType = is_string($result['type']) && $result['type'] !== ''
                ? (string) $result['type']
                : Message::TYPE_TEXT;
            $assistantContent = $result['content'];
            $assistantContent = is_string($assistantContent)
                ? $assistantContent
                : (is_scalar($assistantContent) ? (string) $assistantContent : '');

            $assistantMessage = Message::create([
                'conversation_id' => (string) $message->conversation_id,
                'user_id' => $userId,
                'role' => Message::ROLE_ASSISTANT,
                'type' => $assistantType,
                'content' => $assistantContent,
                'body' => $assistantContent,
                'attachment' => null,
                'read_at' => null,
            ]);

            return ApiResponse::created(
                (new MessageResource($assistantMessage->loadMissing('sender')))->resolve($request),
                'Pesan berhasil dikirim.',
            );
        } catch (HaloSitekAIException $exception) {
            Log::error('HaloSitek AI chat request failed.', [
                'user_id' => $userId,
                'status' => $exception->statusCode(),
                'error' => $exception->getMessage(),
                'context' => $exception->context(),
            ]);

            if ($exception->statusCode() === 503) {
                return ApiResponse::error(
                    'AI service/Ollama sedang tidak tersedia. Silakan coba lagi beberapa saat.',
                    ApiStatus::SERVICE_UNAVAILABLE
                );
            }

            if ($exception->statusCode() === 422) {
                return ApiResponse::error(
                    'Permintaan tidak dapat diproses oleh AI service. Mohon periksa pesan Anda.',
                    ApiStatus::UNPROCESSABLE_ENTITY
                );
            }

            return ApiResponse::error(
                'Terjadi kesalahan internal saat memproses permintaan AI.',
                ApiStatus::SERVER_ERROR
            );
        } catch (Throwable $exception) {
            Log::error('Unexpected AI chat integration error.', [
                'user_id' => $userId,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return ApiResponse::error(
                'Terjadi kesalahan internal saat memproses permintaan AI.',
                ApiStatus::SERVER_ERROR
            );
        }
    }

    /**
     * @OA\Post(
     *   path="/chat/ai/messages",
     *   tags={"Chat"},
     *   security={{"BearerAuth":{}}},
     *   summary="Send AI message (legacy endpoint)",
     *   description="Mengirim pesan ke AI service dengan history berbasis user (endpoint legacy).",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       required={"message"},
     *
     *       @OA\Property(property="message", type="string", maxLength=2000, example="Buatkan konsep rumah tropis.")
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="AI response berhasil",
     *
     *     @OA\JsonContent(
     *       example={
     *         "type": "text",
     *         "content": "Ini adalah jawaban AI.",
     *         "prompt_used": null
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=503, ref="#/components/responses/ServiceUnavailableError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function storeAi(Request $request, HaloSitekAIService $ai): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $userId = (string) ($user->getAttribute('_id') ?? $user->getKey());

        $history = Message::query()
            ->where('user_id', $userId)
            ->whereIn('role', ['user', 'assistant'])
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->reverse()
            ->map(function (Message $message): array {
                $content = $message->content;

                if (! is_string($content) || $content === '') {
                    $content = is_string($message->body) ? $message->body : '';
                }

                return [
                    'role' => (string) $message->role,
                    'content' => $content,
                ];
            })
            ->filter(fn (array $message): bool => in_array($message['role'], ['user', 'assistant'], true) && $message['content'] !== '')
            ->values()
            ->all();

        try {
            $result = $ai->generate(
                userId: $userId,
                message: $validated['message'],
                history: $history,
            );

            Message::create([
                'user_id' => $userId,
                'role' => 'user',
                'type' => 'text',
                'content' => $validated['message'],
                'body' => $validated['message'],
                'attachment' => null,
                'read_at' => null,
            ]);

            $assistantType = is_string($result['type']) && $result['type'] !== ''
                ? (string) $result['type']
                : 'text';
            $assistantContent = $result['content'];
            $assistantContent = is_string($assistantContent)
                ? $assistantContent
                : (is_scalar($assistantContent) ? (string) $assistantContent : '');

            Message::create([
                'user_id' => $userId,
                'role' => 'assistant',
                'type' => $assistantType,
                'content' => $assistantContent,
                'body' => $assistantContent,
                'attachment' => null,
                'read_at' => null,
            ]);

            return response()->json($result);
        } catch (HaloSitekAIException $exception) {
            Log::error('HaloSitek AI chat request failed.', [
                'user_id' => $userId,
                'status' => $exception->statusCode(),
                'error' => $exception->getMessage(),
                'context' => $exception->context(),
            ]);

            if ($exception->statusCode() === 503) {
                return ApiResponse::error(
                    'AI service/Ollama sedang tidak tersedia. Silakan coba lagi beberapa saat.',
                    ApiStatus::SERVICE_UNAVAILABLE
                );
            }

            if ($exception->statusCode() === 422) {
                return ApiResponse::error(
                    'Permintaan tidak dapat diproses oleh AI service. Mohon periksa pesan Anda.',
                    ApiStatus::UNPROCESSABLE_ENTITY
                );
            }

            return ApiResponse::error(
                'Terjadi kesalahan internal saat memproses permintaan AI.',
                ApiStatus::SERVER_ERROR
            );
        } catch (Throwable $exception) {
            Log::error('Unexpected AI chat integration error.', [
                'user_id' => $userId,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return ApiResponse::error(
                'Terjadi kesalahan internal saat memproses permintaan AI.',
                ApiStatus::SERVER_ERROR
            );
        }
    }

    private function shouldQueueAiRequest(string $message): bool
    {
        $normalized = mb_strtolower($message);
        $imageKeywords = [
            'gambar',
            'gambarkan',
            'visualisasi',
            'denah',
            'desain',
            'render',
            'sketsa',
            'layout',
            'floor plan',
            'fasad',
            '3d',
            'interior',
            'eksterior',
        ];

        return Str::contains($normalized, $imageKeywords);
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

        $consultation = $conversation->consultation_id
            ? Consultation::find((string) $conversation->consultation_id)
            : null;
        if ($consultation instanceof Consultation && ! $consultation->isSessionActive()) {
            return ApiResponse::validationError([
                'conversation_id' => ['Sesi konsultasi sudah berakhir, chat hanya dapat dibaca.'],
            ]);
        }

        $isTyping = (bool) $request->boolean('is_typing', true);

        broadcast(new TypingIndicator((string) $conversation->getKey(), $userId, $isTyping))->toOthers();

        return ApiResponse::success(message: 'Status mengetik berhasil dikirim.');
    }
}
