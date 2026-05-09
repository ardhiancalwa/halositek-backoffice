<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Consultation;
use App\Models\ConsultationReport;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsultationsController extends Controller
{
    public function index(): Factory|View
    {
        return view('admin.pages.dashboard.consultations.index');
    }

    public function reportData(Request $request): JsonResponse
    {
        $query = ConsultationReport::query()
            ->with(['consultation', 'requester', 'opposingParty'])
            ->latest();

        if ($request->filled('role') && $request->input('role') !== 'all') {
            $role = $request->string('role')->toString();

            if (in_array($role, ['user', 'architect'], true)) {
                $query->where('requester_role', $role);
            }
        }

        $perPage = min(50, max(1, (int) $request->input('per_page', 10)));
        $reports = $query->paginate($perPage);

        $items = $reports->getCollection()->map(function (ConsultationReport $report) {
            $consultation = $report->consultation;
            $requester = $report->requester;
            $opposingParty = $report->opposingParty;

            return [
                'id' => $report->id,
                'requester_name' => $requester?->name ?? 'Unknown',
                'requester_role' => $report->requester_role ?? 'user',
                'requester_avatar' => $requester?->photo_profile_url
                    ?? 'https://ui-avatars.com/api/?name=' . urlencode($requester?->name ?? 'U') . '&background=ececec&color=333333&rounded=true&bold=true',
                'reason' => $report->reason ?? '-',
                'consultation_date' => $consultation?->consultation_date?->format('M d, Y') ?? '-',
                'opposing_party_name' => $opposingParty?->name ?? 'Unknown',
                'opposing_party_avatar' => $opposingParty?->photo_profile_url
                    ?? 'https://ui-avatars.com/api/?name=' . urlencode($opposingParty?->name ?? 'U') . '&background=ececec&color=333333&rounded=true&bold=true',
                'session_fee' => $consultation?->session_fee ?? 0,
                'action_status' => $report->action_status ?? 'pending',
                'consultation_id' => $consultation?->id,
            ];
        });

        return ApiResponse::paginatedItems($items->toArray(), $reports, 'Reports retrieved successfully.');
    }

    public function payrollData(Request $request): JsonResponse
    {
        $filter = $request->input('filter', 'all');

        $query = Consultation::query()
            ->with('architect')
            ->where('status', 'completed');

        if ($filter === 'pay') {
            $query->where('payout_status', 'pending');
        } elseif ($filter === 'history') {
            $query->where('payout_status', 'released');
        }

        $consultations = $query->get();

        $grouped = $consultations->groupBy('architect_id');
        $queueRows = $grouped->map(function ($items, $architectId) {
            $first = $items->first();
            $architect = $first->architect;
            $totalConsultation = $items->count();
            $totalEarnings = (int) $items->sum('session_fee');
            $perSession = $totalConsultation > 0 ? (int) round($totalEarnings / $totalConsultation) : 0;

            return [
                'architect_id' => (string) $architectId,
                'architect_name' => $architect?->name ?? 'Unknown',
                'architect_avatar' => $architect?->photo_profile_url
                    ?? 'https://ui-avatars.com/api/?name=' . urlencode($architect?->name ?? 'A') . '&background=ececec&color=333333&rounded=true&bold=true',
                'total_earnings' => $totalEarnings,
                'per_session' => $perSession,
                'total_consultations' => $totalConsultation,
                'payout_status' => $first->payout_status ?? 'pending',
            ];
        })->values();

        $perPage = min(50, max(1, (int) $request->input('per_page', 10)));
        $page = max(1, (int) $request->input('page', 1));
        $total = $queueRows->count();
        $items = $queueRows->forPage($page, $perPage)->values()->all();

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Payroll data retrieved successfully.',
            'data' => $items,
            'meta' => [
                'current_page' => $page,
                'last_page' => max(1, (int) ceil($total / $perPage)),
                'per_page' => $perPage,
                'total' => $total,
            ],
        ]);
    }
}
