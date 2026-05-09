<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Consultation;
use App\Models\ConsultationReport;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ConsultationsController extends Controller
{
    public function index(): Factory|View
    {
        return view('admin.pages.dashboard.consultations.index');
    }

    public function reportStats(): JsonResponse
    {
        $total = ConsultationReport::query()->count();
        $new = ConsultationReport::query()->where('action_status', 'new')->count();
        $user = ConsultationReport::query()->where('requester_role', 'user')->count();
        $architect = ConsultationReport::query()->where('requester_role', 'architect')->count();

        return ApiResponse::success([
            'total_report' => $total,
            'new_report' => $new,
            'user_report' => $user,
            'architect_report' => $architect,
        ]);
    }

    public function payrollSummary(): JsonResponse
    {
        $pendingAmount = Consultation::query()
            ->where('status', 'completed')
            ->where('payout_status', 'pending')
            ->sum('session_fee');

        return ApiResponse::success([
            'pending_payouts' => (int) $pendingAmount,
        ]);
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

        $items = $reports->getCollection()->map(function (ConsultationReport $report): array {
            $consultation = $report->consultation;
            $requester = $report->requester;
            $opposingParty = $report->opposingParty;

            return [
                'id' => $report->id,
                'requester_name' => $requester->name ?? 'Unknown',
                'requester_role' => $report->requester_role ?? 'user',
                'requester_avatar' => $requester->photo_profile_url,
                'reason' => $report->reason ?? '-',
                'consultation_date' => $consultation?->consultation_date
                    ? Carbon::parse($consultation->consultation_date)->format('M d, Y')
                    : '-',
                'opposing_party_name' => $opposingParty->name ?? 'Unknown',
                'opposing_party_avatar' => $opposingParty->photo_profile_url,
                'session_fee' => (int) ($consultation->session_fee ?? 0),
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
        /** @var Collection<int, array<string, mixed>> $queueRows */
        $queueRows = $grouped->map(function ($items, $architectId): array {
            $first = $items->first();
            $architect = $first->architect;
            $totalConsultation = $items->count();
            $totalEarnings = (int) $items->sum('session_fee');
            $perSession = $totalConsultation > 0 ? (int) round($totalEarnings / $totalConsultation) : 0;

            return [
                'architect_id' => (string) $architectId,
                'architect_name' => $architect->name ?? 'Unknown',
                'architect_avatar' => $architect->photo_profile_url,
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

    public function architectConsultations(string $architectId, Request $request): JsonResponse
    {
        $status = $request->input('status', 'pending'); // pending or released

        $consultations = Consultation::query()
            ->with('user')
            ->where('architect_id', $architectId)
            ->where('status', 'completed')
            ->where('payout_status', $status)
            ->get();

        $items = $consultations->map(function (Consultation $consultation): array {
            return [
                'user_name' => $consultation->user?->name ?? 'Unknown',
                'date' => $consultation->consultation_date ? Carbon::parse($consultation->consultation_date)->format('M d, Y') : '-',
                'fee' => (int) ($consultation->session_fee ?? 0),
                'status' => 'Verified',
            ];
        });

        $totalAmount = (int) $consultations->sum('session_fee');
        $perSession = $consultations->count() > 0 ? (int) round($totalAmount / $consultations->count()) : 0;

        return ApiResponse::success([
            'items' => $items,
            'summary' => [
                'total_amount' => $totalAmount,
                'per_session' => $perSession,
                'total_consultations' => $consultations->count(),
            ],
        ], 'Architect consultations retrieved successfully.');
    }

    public function releasePayroll(string $architectId): JsonResponse
    {
        $updated = Consultation::query()
            ->where('architect_id', $architectId)
            ->where('status', 'completed')
            ->where('payout_status', 'pending')
            ->update(['payout_status' => 'released']);

        if ($updated === 0) {
            return ApiResponse::notFound('No pending payouts found for this architect.');
        }

        return ApiResponse::success(null, 'Payroll released successfully.');
    }
}
