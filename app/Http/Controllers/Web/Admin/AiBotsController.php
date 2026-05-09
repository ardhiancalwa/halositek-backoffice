<?php

namespace App\Http\Controllers\Web\Admin;

use App\Actions\AiChatbot\GetAiChatbotPerformanceAction;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\AiChatbotLog;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiBotsController extends Controller
{
    public function index(): Factory|View
    {
        return view('admin.pages.dashboard.ai-bots.index');
    }

    public function performanceStats(GetAiChatbotPerformanceAction $getPerformance): JsonResponse
    {
        return ApiResponse::success($getPerformance->execute());
    }

    public function logsData(Request $request): JsonResponse
    {
        $query = AiChatbotLog::query()
            ->with('user')
            ->whereNotNull('user_id')
            ->latest();

        $status = $request->input('status');
        if (is_string($status) && in_array($status, ['success', 'failed'], true)) {
            $query->where('status', $status);
        }

        $perPage = min(50, max(1, (int) $request->input('per_page', 10)));
        $logs = $query->paginate($perPage);

        $items = $logs->getCollection()
            ->map(function (AiChatbotLog $log): array {
                $user = $log->user;

                return [
                    'id' => (string) $log->getKey(),
                    'user_name' => $user?->name ?? 'Unknown',
                    'user_avatar' => $user?->photo_profile_url
                        ?? 'https://ui-avatars.com/api/?name=' . urlencode($user?->name ?? 'U') . '&background=ececec&color=333333&rounded=true&bold=true',
                    'date' => $log->created_at?->format('M d, Y') ?? '-',
                    'prompt_preview' => (string) $log->prompt_preview,
                    'status' => (string) $log->status,
                    'generate_time_ms' => (int) $log->generate_time_ms,
                    'request_payload' => (string) ($log->request_payload ?? ''),
                    'result_type' => (string) ($log->result_type ?? 'text'),
                    'generated_text' => $log->generated_text,
                    'generated_image_url' => $log->generated_image_url,
                    'error_log' => (string) ($log->error_log ?? ''),
                ];
            })
            ->values()
            ->all();

        return ApiResponse::paginatedItems($items, $logs, 'AI Bot logs retrieved successfully.');
    }
}
