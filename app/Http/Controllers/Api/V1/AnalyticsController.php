<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use OpenApi\Annotations as OA;

class AnalyticsController extends Controller
{
    /**
     * @OA\Get(
     *   path="/analytics/overview",
     *   tags={"Analytics"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get analytics overview",
     *   description="Returns overview metrics for registered users, approved architects, and active projects.",
     *
     *   @OA\Response(response=200, description="Analytics overview retrieved successfully.",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Analytics overview retrieved successfully.", "data": {"registered_user": 120, "registered_architect": 42, "active_projects": 88}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function overview(): JsonResponse
    {
        $registeredUsers = User::query()
            ->where('role', UserRole::User->value)
            ->count();

        $registeredArchitects = User::query()
            ->where('role', UserRole::Architect->value)
            ->whereHas('architectProfile', function ($query): void {
                $query->where('status', 'approved');
            })
            ->count();

        $activeProjects = Project::query()
            ->where('status', ProjectStatus::Approved->value)
            ->count();

        return ApiResponse::success([
            'registered_user' => $registeredUsers,
            'registered_architect' => $registeredArchitects,
            'active_projects' => $activeProjects,
        ], 'Analytics overview retrieved successfully.');
    }

    /**
     * @OA\Get(
     *   path="/analytics/user-growth",
     *   tags={"Analytics"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get user growth",
     *   description="Returns user growth series based on selected range.",
     *
     *   @OA\Parameter(name="range", in="query", required=false, @OA\Schema(type="string", enum={"today","last_7_days","last_month"}, default="last_7_days")),
     *
     *   @OA\Response(response=200, description="User growth retrieved successfully.",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "User growth retrieved successfully.", "data": {"range": "last_7_days", "labels": {"2026-04-20", "2026-04-21", "2026-04-22", "2026-04-23", "2026-04-24", "2026-04-25", "2026-04-26"}, "series": {2, 1, 0, 3, 1, 2, 1}, "cumulative_series": {2, 3, 3, 6, 7, 9, 10}, "total": 10}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function userGrowth(Request $request): JsonResponse
    {
        $range = $this->resolveRange($request);

        $users = User::query()
            ->where('role', UserRole::User->value)
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->get(['created_at']);

        return ApiResponse::success(
            $this->buildGrowthPayload($users, $range),
            'User growth retrieved successfully.',
        );
    }

    /**
     * @OA\Get(
     *   path="/analytics/architect-growth",
     *   tags={"Analytics"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get architect growth",
     *   description="Returns approved architect growth series based on selected range.",
     *
     *   @OA\Parameter(name="range", in="query", required=false, @OA\Schema(type="string", enum={"today","last_7_days","last_month"}, default="last_7_days")),
     *
     *   @OA\Response(response=200, description="Architect growth retrieved successfully.",
     *
     *   @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Architect growth retrieved successfully.", "data": {"range": "last_month", "labels": {"Apr 1 - Apr 7", "Apr 8 - Apr 14", "Apr 15 - Apr 21", "Apr 22 - Apr 24"}, "series": {0, 1, 0, 2}, "cumulative_series": {0, 1, 1, 3}, "total": 3}})
     * ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function architectGrowth(Request $request): JsonResponse
    {
        $range = $this->resolveRange($request);

        $architects = User::query()
            ->where('role', UserRole::Architect->value)
            ->whereHas('architectProfile', function ($query): void {
                $query->where('status', 'approved');
            })
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->get(['created_at']);

        return ApiResponse::success(
            $this->buildGrowthPayload($architects, $range),
            'Architect growth retrieved successfully.',
        );
    }

    /**
     * @return array{range:string,start:Carbon,end:Carbon,unit:string,labels:array<int,string>}
     */
    private function resolveRange(Request $request): array
    {
        $validated = $request->validate([
            'range' => ['nullable', 'string', 'in:today,last_7_days,last_month'],
        ]);

        $range = $validated['range'] ?? 'last_7_days';
        $now = now();

        if ($range === 'today') {
            $start = $now->copy()->startOfDay();
            $end = $now->copy()->endOfDay();
            $unit = '3hour';
        } elseif ($range === 'last_month') {
            $start = $now->copy()->startOfMonth();
            $end = $now->copy();
            $unit = 'week';
        } else {
            $start = $now->copy()->startOfWeek();
            $end = $now->copy()->endOfWeek();
            $unit = 'day';
        }

        $labels = $this->buildLabels($start, $end, $unit);

        return [
            'range' => $range,
            'start' => $start,
            'end' => $end,
            'unit' => $unit,
            'labels' => $labels,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function buildLabels(Carbon $start, Carbon $end, string $unit): array
    {
        $labels = [];

        if ($unit === '3hour') {
            $period = CarbonPeriod::create($start->copy()->startOfDay(), '3 hours', $start->copy()->endOfDay());
            foreach ($period as $date) {
                $labels[] = $date->format('H:00');
            }
        } elseif ($unit === 'week') {
            $current = $start->copy()->startOfWeek();
            while ($current->lte($end)) {
                $weekEnd = $current->copy()->endOfWeek();
                if ($weekEnd->gt($end)) {
                    $weekEnd = $end->copy();
                }
                $labels[] = $current->format('M j') . ' - ' . $weekEnd->format('M j');
                $current = $current->addWeek();
            }
        } else {
            $period = CarbonPeriod::create($start->copy()->startOfDay(), '1 day', $end->copy()->startOfDay());
            foreach ($period as $date) {
                $labels[] = $date->format('D');
            }
        }

        return $labels;
    }

    /**
     * @param  Collection<int, User>  $items
     * @param  array{range:string,start:Carbon,end:Carbon,unit:string,labels:array<int,string>}  $range
     * @return array{range:string,labels:array<int,string>,series:array<int,int>,cumulative_series:array<int,int>,total:int}
     */
    private function buildGrowthPayload(Collection $items, array $range): array
    {
        if ($range['unit'] === 'week') {
            $counts = $items
                ->filter(static fn (User $item): bool => $item->created_at !== null)
                ->groupBy(function (User $item): string {
                    $date = $item->created_at;
                    $startOfWeek = $date->copy()->startOfWeek();
                    $endOfWeek = $date->copy()->endOfWeek();

                    return $startOfWeek->format('M j') . ' - ' . $endOfWeek->format('M j');
                })
                ->map(static fn (Collection $group): int => $group->count());
        } else {
            $counts = $items
                ->filter(static fn (User $item): bool => $item->created_at !== null)
                ->groupBy(function (User $item) use ($range): string {
                    if ($range['unit'] === '3hour') {
                        $hour = $item->created_at->hour;
                        $bucketHour = (int) ($hour / 3) * 3;

                        return sprintf('%02d:00', $bucketHour);
                    }

                    return $item->created_at->format('D');
                })
                ->map(static fn (Collection $group): int => $group->count());
        }

        $series = array_map(
            static fn (string $label): int => (int) ($counts->get($label) ?? 0),
            $range['labels'],
        );

        $runningTotal = 0;
        $cumulativeSeries = array_map(static function (int $value) use (&$runningTotal): int {
            $runningTotal += $value;

            return $runningTotal;
        }, $series);

        return [
            'range' => $range['range'],
            'labels' => $range['labels'],
            'series' => $series,
            'cumulative_series' => $cumulativeSeries,
            'total' => array_sum($series),
        ];
    }
}
