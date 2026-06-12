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
use App\Models\AiChatbotLog;
use App\Models\Consultation;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\HaloSitekAIService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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
     *   description="Mengirim pesan teks/file ke conversation antar user-arsitek (tanpa proses AI).",
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
     *           "role": "user",
     *           "type": "text",
     *           "content": "Halo, kabar kamu gimana?",
     *           "body": "Halo, kabar kamu gimana?",
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
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function store(SendMessageRequest $request, SendMessageAction $action): JsonResponse
    {
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('chat/attachments', 'public');
        }

        $message = $action->execute(
            SendMessageDTO::fromRequest($request, $attachmentPath),
            $request->user(),
        );

        return ApiResponse::created(
            (new MessageResource($message->loadMissing('sender')))->resolve($request),
            'Pesan berhasil dikirim.',
        );
    }

    /**
     * @OA\Get(
     *   path="/chat/ai/messages",
     *   tags={"Chat"},
     *   security={{"BearerAuth":{}}},
     *   summary="List AI chat history",
     *   description="Mengambil history chat AI milik user login dengan cursor-based pagination. Data terbaru ditampilkan lebih dulu; gunakan next_cursor untuk load history yang lebih lama.",
     *
     *   @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100, example=20)),
     *   @OA\Parameter(name="cursor", in="query", required=false, @OA\Schema(type="string", example="eyJjcmVhdGVkX2F0IjoiMjAyNi0wNC0xOVQxMDowMDowMCswMDowMCIsImlkIjoiMDFKMk1FU1NBR0UwMDEifQ==")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="History chat AI berhasil diambil",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "History chat AI berhasil diambil.",
     *         "data": {
     *           {
     *             "id": "01J2MESSAGE002",
     *             "conversation_id": "",
     *             "user_id": "01J2USERA",
     *             "role": "assistant",
     *             "type": "text",
     *             "content": "Ini adalah jawaban AI.",
     *             "body": "Ini adalah jawaban AI.",
     *             "attachment": null,
     *             "attachment_url": null,
     *             "read_at": null,
     *             "is_mine": false,
     *             "created_at": "2026-04-19T10:00:03+00:00",
     *             "updated_at": "2026-04-19T10:00:03+00:00"
     *           }
     *         },
     *         "meta": {
     *           "per_page": 20,
     *           "next_cursor": null,
     *           "has_more": false
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
    public function aiHistory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'cursor' => ['sometimes', 'string'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $userId = (string) ($user->getAttribute('_id') ?? $user->getKey());
        $perPage = (int) ($validated['per_page'] ?? 20);
        $cursor = $this->decodeAiHistoryCursor($validated['cursor'] ?? null);

        $query = Message::query()
            ->where('user_id', $userId)
            ->whereIn('role', [Message::ROLE_USER, Message::ROLE_ASSISTANT])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if ($cursor !== null) {
            $query->where(function ($query) use ($cursor): void {
                $query
                    ->where('created_at', '<', $cursor['created_at'])
                    ->orWhere(function ($query) use ($cursor): void {
                        $query
                            ->where('created_at', '=', $cursor['created_at'])
                            ->where('id', '<', $cursor['id']);
                    });
            });
        }

        /** @var Collection<int, Message> $messages */
        $messages = $query->limit($perPage + 1)->get();
        $hasMore = $messages->count() > $perPage;
        $items = $messages->take($perPage)->values();
        $lastItem = $items->last();

        $nextCursor = $hasMore && $lastItem instanceof Message
            ? $this->encodeAiHistoryCursor($lastItem)
            : null;

        return response()->json([
            'success' => true,
            'status_code' => ApiStatus::SUCCESS->value,
            'message' => ApiStatus::SUCCESS->message('History chat AI berhasil diambil.'),
            'data' => MessageResource::collection($items)->resolve($request),
            'meta' => [
                'per_page' => $perPage,
                'next_cursor' => $nextCursor,
                'has_more' => $hasMore,
            ],
        ]);
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
        $messageText = (string) $validated['message'];
        $generationId = (string) Str::uuid();
        $startedAt = microtime(true);

        Cache::put($this->aiActiveGenerationCacheKey($userId), $generationId, now()->addMinutes(10));
        Cache::forget($this->aiCancelledGenerationCacheKey($userId, $generationId));

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
                message: $messageText,
                history: $history,
                generationId: $generationId,
            );

            if ($this->isAiGenerationStopped($userId, $generationId)) {
                $this->recordAiChatbotLog(
                    userId: $userId,
                    requestMessage: $messageText,
                    status: 'failed',
                    startedAt: $startedAt,
                    errorLog: 'Generate AI dihentikan oleh user.'
                );

                return ApiResponse::error('Generate AI dihentikan.', ApiStatus::CONFLICT);
            }

            Message::create([
                'user_id' => $userId,
                'role' => 'user',
                'type' => 'text',
                'content' => $messageText,
                'body' => $messageText,
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

            $this->recordAiChatbotLog(
                userId: $userId,
                requestMessage: $messageText,
                status: 'success',
                startedAt: $startedAt,
                result: $result
            );

            return response()->json($result);
        } catch (HaloSitekAIException $exception) {
            Log::error('HaloSitek AI chat request failed.', [
                'user_id' => $userId,
                'status' => $exception->statusCode(),
                'error' => $exception->getMessage(),
                'context' => $exception->context(),
            ]);

            $errorMessage = $this->isAiGenerationStopped($userId, $generationId)
                ? 'Generate AI dihentikan oleh user.'
                : $exception->getMessage();

            $this->recordAiChatbotLog(
                userId: $userId,
                requestMessage: $messageText,
                status: 'failed',
                startedAt: $startedAt,
                errorLog: $errorMessage
            );

            if ($this->isAiGenerationStopped($userId, $generationId)) {
                return ApiResponse::error('Generate AI dihentikan.', ApiStatus::CONFLICT);
            }

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

            $this->recordAiChatbotLog(
                userId: $userId,
                requestMessage: $messageText,
                status: 'failed',
                startedAt: $startedAt,
                errorLog: $this->isAiGenerationStopped($userId, $generationId)
                    ? 'Generate AI dihentikan oleh user.'
                    : $exception->getMessage()
            );

            if ($this->isAiGenerationStopped($userId, $generationId)) {
                return ApiResponse::error('Generate AI dihentikan.', ApiStatus::CONFLICT);
            }

            return ApiResponse::error(
                'Terjadi kesalahan internal saat memproses permintaan AI.',
                ApiStatus::SERVER_ERROR
            );
        } finally {
            $this->clearAiGenerationState($userId, $generationId);
        }
    }

    /**
     * @OA\Post(
     *   path="/chat/ai/stop",
     *   tags={"Chat"},
     *   security={{"BearerAuth":{}}},
     *   summary="Stop active AI generation",
     *   description="Menghentikan generate AI aktif milik user login. Backend menandai request sebagai cancelled dan mencoba meneruskan stop signal ke AI service.",
     *
     *   @OA\Response(
     *     response=200,
     *     description="Stop signal processed",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "Generate AI berhasil dihentikan.",
     *         "data": {"stopped": true}
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function stopAi(Request $request, HaloSitekAIService $ai): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $userId = (string) ($user->getAttribute('_id') ?? $user->getKey());
        $activeGenerationId = Cache::get($this->aiActiveGenerationCacheKey($userId));

        if (! is_string($activeGenerationId) || $activeGenerationId === '') {
            return ApiResponse::success([
                'stopped' => false,
            ], 'Tidak ada generate AI yang sedang berjalan.');
        }

        Cache::put($this->aiCancelledGenerationCacheKey($userId, $activeGenerationId), true, now()->addMinutes(10));

        $ai->stopGeneration($userId, $activeGenerationId);

        return ApiResponse::success([
            'stopped' => true,
        ], 'Generate AI berhasil dihentikan.');
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

    /**
     * @return array{created_at: Carbon, id: string}|null
     */
    private function decodeAiHistoryCursor(?string $cursor): ?array
    {
        if ($cursor === null || $cursor === '') {
            return null;
        }

        $decoded = base64_decode($cursor, true);
        $payload = $decoded !== false ? json_decode($decoded, true) : null;

        if (! is_array($payload) || ! is_string($payload['created_at'] ?? null) || ! is_string($payload['id'] ?? null)) {
            throw ValidationException::withMessages([
                'cursor' => ['Cursor tidak valid.'],
            ]);
        }

        try {
            $createdAt = Carbon::parse($payload['created_at']);
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'cursor' => ['Cursor tidak valid.'],
            ]);
        }

        return [
            'created_at' => $createdAt,
            'id' => $payload['id'],
        ];
    }

    private function encodeAiHistoryCursor(Message $message): ?string
    {
        if ($message->created_at === null) {
            return null;
        }

        return base64_encode((string) json_encode([
            'created_at' => $message->created_at->toIso8601String(),
            'id' => (string) $message->getKey(),
        ]));
    }

    /**
     * @param  array<string, mixed>|null  $result
     */
    private function recordAiChatbotLog(
        string $userId,
        string $requestMessage,
        string $status,
        float $startedAt,
        ?array $result = null,
        ?string $errorLog = null
    ): void {
        $generateTimeMs = max(0, (int) round((microtime(true) - $startedAt) * 1000));
        $resultType = is_string($result['type'] ?? null) && $result['type'] !== ''
            ? (string) $result['type']
            : null;
        $content = $result['content'] ?? null;
        $content = is_string($content)
            ? $content
            : (is_scalar($content) ? (string) $content : null);

        AiChatbotLog::create([
            'user_id' => $userId,
            'prompt_preview' => Str::limit($requestMessage, 120, ''),
            'request_payload' => $requestMessage,
            'status' => $status,
            'generate_time_ms' => $generateTimeMs,
            'result_type' => $resultType,
            'generated_text' => $resultType === Message::TYPE_TEXT ? $content : null,
            'generated_image_url' => $resultType === Message::TYPE_IMAGE ? $content : null,
            'error_log' => $errorLog,
        ]);
    }

    private function isAiGenerationStopped(string $userId, string $generationId): bool
    {
        return Cache::has($this->aiCancelledGenerationCacheKey($userId, $generationId));
    }

    private function clearAiGenerationState(string $userId, string $generationId): void
    {
        if (Cache::get($this->aiActiveGenerationCacheKey($userId)) === $generationId) {
            Cache::forget($this->aiActiveGenerationCacheKey($userId));
        }

        Cache::forget($this->aiCancelledGenerationCacheKey($userId, $generationId));
    }

    private function aiActiveGenerationCacheKey(string $userId): string
    {
        return "ai-generation-active:{$userId}";
    }

    private function aiCancelledGenerationCacheKey(string $userId, string $generationId): string
    {
        return "ai-generation-cancelled:{$userId}:{$generationId}";
    }
}
