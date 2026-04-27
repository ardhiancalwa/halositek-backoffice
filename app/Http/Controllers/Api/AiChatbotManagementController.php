<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AiLogFilterRequest;
use App\Http\Responses\ApiResponse;
use App\Models\AiChatbotLog;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class AiChatbotManagementController extends Controller
{
    /**
     * @OA\Get(
     *   path="/ai-chatbot/performance",
     *   tags={"AI Chatbot Management"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get AI chatbot real-time performance",
     *
     *   @OA\Response(
     *     response=200,
     *     description="Performance metrics retrieved",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "Operation successful.",
     *         "data": {
     *           "total_generate": 320,
     *           "total_success": 300,
     *           "total_failed": 20
     *         }
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function performance(): JsonResponse
    {
        return ApiResponse::success([
            'total_generate' => AiChatbotLog::query()->count(),
            'total_success' => AiChatbotLog::query()->where('status', 'success')->count(),
            'total_failed' => AiChatbotLog::query()->where('status', 'failed')->count(),
        ]);
    }

    /**
     * @OA\Get(
     *   path="/ai-chatbot/logs",
     *   tags={"AI Chatbot Management"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get AI chatbot activity logs",
     *
     *   @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"success","failed"})),
     *   @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100)),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Activity logs retrieved",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "Log aktivitas AI chatbot berhasil diambil.",
     *         "data": {
     *           {
     *             "id": "01J3AILOG0001",
     *             "user": {"id": "01J3USER001", "name": "Budi Santoso"},
     *             "date": "2026-04-27T08:10:00+00:00",
     *             "prompt_preview": "Generate konsep fasad rumah modern tropis.",
     *             "status": "success",
     *             "generate_time_ms": 1810
     *           }
     *         },
     *         "meta": {"current_page": 1, "last_page": 1, "per_page": 15, "total": 1},
     *         "links": {
     *           "first_page_url": "http://localhost:8000/api/v1/ai-chatbot/logs?page=1",
     *           "last_page_url": "http://localhost:8000/api/v1/ai-chatbot/logs?page=1",
     *           "next_page_url": null,
     *           "prev_page_url": null
     *         }
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function activityLogs(AiLogFilterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? 15);
        $status = $validated['status'] ?? null;

        $query = AiChatbotLog::query()
            ->with('user')
            ->latest();

        if (is_string($status) && $status !== '') {
            $query->where('status', $status);
        }

        $logs = $query->paginate($perPage);
        $logs->setCollection(
            $logs->getCollection()->map(function (AiChatbotLog $log): array {
                return [
                    'id' => (string) $log->getKey(),
                    'user' => [
                        'id' => (string) ($log->user?->getKey() ?? ''),
                        'name' => (string) ($log->user?->name ?? 'Unknown'),
                    ],
                    'date' => $log->created_at?->toIso8601String(),
                    'prompt_preview' => (string) $log->prompt_preview,
                    'status' => (string) $log->status,
                    'generate_time_ms' => (int) $log->generate_time_ms,
                ];
            })
        );

        return ApiResponse::paginated($logs, 'Log aktivitas AI chatbot berhasil diambil.');
    }

    /**
     * @OA\Get(
     *   path="/ai-chatbot/logs/{logId}",
     *   tags={"AI Chatbot Management"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get AI chatbot activity log detail",
     *
     *   @OA\Parameter(name="logId", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Activity log detail retrieved",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "Detail log aktivitas AI chatbot berhasil diambil.",
     *         "data": {
     *           "id": "01J3AILOG0002",
     *           "status": "failed",
     *           "user": {"id": "01J3USER001", "name": "Budi Santoso"},
     *           "date": "2026-04-27T09:10:00+00:00",
     *           "prompt_preview": "Generate image masterplan urban mixed-use.",
     *           "generate_time_ms": 620,
     *           "original_request": "Generate image masterplan urban mixed-use detail...",
     *           "system_error_log": "Inference timeout on provider A."
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
    public function showActivityLog(string $logId): JsonResponse
    {
        $log = AiChatbotLog::query()->with('user')->findOrFail($logId);

        $detail = [
            'id' => (string) $log->getKey(),
            'status' => (string) $log->status,
            'user' => [
                'id' => (string) ($log->user?->getKey() ?? ''),
                'name' => (string) ($log->user?->name ?? 'Unknown'),
            ],
            'date' => $log->created_at?->toIso8601String(),
            'prompt_preview' => (string) $log->prompt_preview,
            'generate_time_ms' => (int) $log->generate_time_ms,
            'original_request' => (string) ($log->request_payload ?? ''),
        ];

        if ((string) $log->status === 'failed') {
            $detail['system_error_log'] = (string) ($log->error_log ?? '');
        } else {
            $detail['generate_result'] = [
                'type' => (string) ($log->result_type ?? 'text'),
                'text' => $log->generated_text,
                'image_url' => $log->generated_image_url,
            ];
        }

        return ApiResponse::success($detail, 'Detail log aktivitas AI chatbot berhasil diambil.');
    }
}
