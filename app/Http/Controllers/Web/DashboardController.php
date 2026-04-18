<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function showDashboard(): Factory|View
    {
        return view('admin.pages.dashboard.index');
    }

    public function showDesigns(): Factory|View
    {
        return view('admin.pages.dashboard.design.index');
    }

    public function showConsultations(): Factory|View
    {
        return view('admin.pages.dashboard.consultations.index');
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
}
