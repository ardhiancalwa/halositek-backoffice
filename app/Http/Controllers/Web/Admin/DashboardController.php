<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ArchitectProfile;
use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): Factory|View
    {
        return view('admin.pages.dashboard.index');
    }

    public function dashboardStats(): JsonResponse
    {
        $totalUsers = User::query()->count();

        $totalArchitects = User::query()
            ->where('role', UserRole::Architect->value)
            ->whereHas('architectProfile', function ($query): void {
                $query->where('status', 'approved');
            })
            ->count();

        $totalDesigns = Project::query()->count();

        return response()->json([
            'data' => [
                'total_users' => $totalUsers,
                'total_architects' => $totalArchitects,
                'total_designs' => $totalDesigns,
            ],
        ]);
    }

    public function userGrowth(Request $request): JsonResponse
    {
        $period = $request->string('period')->toString();
        $period = in_array($period, ['7d', '30d'], true) ? $period : '7d';

        $days = $period === '30d' ? 30 : 7;
        $startDate = now()->startOfDay()->subDays($days - 1);

        $countsByDate = collect(range(0, $days - 1))
            ->mapWithKeys(fn (int $offset): array => [$startDate->copy()->addDays($offset)->toDateString() => 0])
            ->all();

        $users = User::query()
            ->where('created_at', '>=', $startDate)
            ->get(['created_at']);

        foreach ($users as $user) {
            $createdAt = data_get($user, 'created_at');

            if ($createdAt === null) {
                continue;
            }

            $dateKey = Carbon::parse($createdAt)->startOfDay()->toDateString();

            if (array_key_exists($dateKey, $countsByDate)) {
                $countsByDate[$dateKey]++;
            }
        }

        $labels = collect(array_keys($countsByDate))
            ->map(fn (string $date): string => $period === '30d'
                ? Carbon::parse($date)->format('d M')
                : Carbon::parse($date)->format('D'))
            ->values();

        $values = collect(array_values($countsByDate));

        return response()->json([
            'data' => [
                'period' => $period,
                'summary' => [
                    'total_new_users' => $values->sum(),
                    'peak_new_users' => $values->max(),
                ],
                'chart' => [
                    'labels' => $labels,
                    'values' => $values,
                ],
            ],
        ]);
    }

    public function architectGrowth(Request $request): JsonResponse
    {
        $period = $request->string('period')->toString();
        $period = in_array($period, ['7d', '30d'], true) ? $period : '7d';

        $days = $period === '30d' ? 30 : 7;
        $startDate = now()->startOfDay()->subDays($days - 1);

        $countsByDate = collect(range(0, $days - 1))
            ->mapWithKeys(fn (int $offset): array => [$startDate->copy()->addDays($offset)->toDateString() => 0])
            ->all();

        $architectProfiles = ArchitectProfile::query()
            ->where('status', 'approved')
            ->where('created_at', '>=', $startDate)
            ->get(['created_at']);

        foreach ($architectProfiles as $architectProfile) {
            $createdAt = data_get($architectProfile, 'created_at');

            if ($createdAt === null) {
                continue;
            }

            $dateKey = Carbon::parse($createdAt)->startOfDay()->toDateString();

            if (array_key_exists($dateKey, $countsByDate)) {
                $countsByDate[$dateKey]++;
            }
        }

        $labels = collect(array_keys($countsByDate))
            ->map(fn (string $date): string => $period === '30d'
                ? Carbon::parse($date)->format('d M')
                : Carbon::parse($date)->format('D'))
            ->values();

        $values = collect(array_values($countsByDate));

        return response()->json([
            'data' => [
                'period' => $period,
                'summary' => [
                    'total_new_architects' => $values->sum(),
                    'peak_new_architects' => $values->max(),
                ],
                'chart' => [
                    'labels' => $labels,
                    'values' => $values,
                ],
            ],
        ]);
    }
}
