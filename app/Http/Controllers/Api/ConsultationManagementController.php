<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ConsultationReportActionRequest;
use App\Http\Requests\Api\ConsultationReportFilterRequest;
use App\Http\Requests\Api\PayrollReleaseRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Consultation;
use App\Models\ConsultationReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

class ConsultationManagementController extends Controller
{
    /**
     * @OA\Get(
     *   path="/consultations/reports/stats",
     *   tags={"Consultation Management"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get consultation report statistics",
     *
     *   @OA\Response(
     *     response=200,
     *     description="Report stats retrieved",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "Operation successful.",
     *         "data": {
     *           "total_report": 76,
     *           "new_report": 12,
     *           "user_report": 51,
     *           "architect_report": 25
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
    public function reportStats(): JsonResponse
    {
        return ApiResponse::success([
            'total_report' => ConsultationReport::query()->count(),
            'new_report' => ConsultationReport::query()->where('action_status', 'new')->count(),
            'user_report' => ConsultationReport::query()->where('requester_role', 'user')->count(),
            'architect_report' => ConsultationReport::query()->where('requester_role', 'architect')->count(),
        ]);
    }

    /**
     * @OA\Get(
     *   path="/consultations/reports",
     *   tags={"Consultation Management"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get consultation report list",
     *
     *   @OA\Parameter(name="role", in="query", required=false, @OA\Schema(type="string", enum={"user","architect"})),
     *   @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100)),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Report list retrieved",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "Daftar report berhasil diambil.",
     *         "data": {
     *           {
     *             "id": "01J3REPORT0001",
     *             "requester": {
     *               "id": "01J3USER001",
     *               "name": "Ayu Pratama",
     *               "role": "user",
     *               "photo_profile": "users/profiles/ayu.webp",
     *               "photo_profile_url": "http://localhost:8000/storage/users/profiles/ayu.webp"
     *             },
     *             "reason": "Arsitek tidak hadir pada jadwal konsultasi.",
     *             "consultation_date": "2026-04-27T10:30:00+00:00",
     *             "opposing_party": {
     *               "id": "01J3ARCH001",
     *               "name": "Dimas Arsitek",
     *               "photo_profile": "users/profiles/dimas.webp",
     *               "photo_profile_url": "http://localhost:8000/storage/users/profiles/dimas.webp"
     *             },
     *             "nominal": 300000,
     *             "transcript": "Riwayat chat konsultasi...",
     *             "action_report": "new"
     *           }
     *         },
     *         "meta": {"current_page": 1, "last_page": 1, "per_page": 15, "total": 1},
     *         "links": {
     *           "first_page_url": "http://localhost:8000/api/v1/consultations/reports?page=1",
     *           "last_page_url": "http://localhost:8000/api/v1/consultations/reports?page=1",
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
    public function reportList(ConsultationReportFilterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? 15);
        $role = $validated['role'] ?? null;

        $query = ConsultationReport::query()
            ->with(['consultation', 'requester', 'opposingParty'])
            ->latest();

        if (is_string($role) && $role !== '') {
            $query->where('requester_role', $role);
        }

        $reports = $query->paginate($perPage);

        $items = $reports->getCollection()
            ->map(function (ConsultationReport $report): array {
                $consultation = $report->consultation;
                $requester = $report->requester;
                $opposing = $report->opposingParty;

                $consultationDate = $consultation?->consultation_date;
                $consultationDateIso = is_string($consultationDate)
                    ? Carbon::parse($consultationDate)->toIso8601String()
                    : null;

                return [
                    'id' => (string) $report->getKey(),
                    'requester' => [
                        'id' => (string) $requester->getKey(),
                        'name' => (string) $requester->name,
                        'role' => (string) $report->requester_role,
                        'photo_profile' => $requester->photo_profile,
                        'photo_profile_url' => $requester->photo_profile
                            ? Storage::url((string) $requester->photo_profile)
                            : null,
                    ],
                    'reason' => (string) $report->reason,
                    'consultation_date' => $consultationDateIso,
                    'opposing_party' => [
                        'id' => (string) $opposing->getKey(),
                        'name' => (string) $opposing->name,
                        'photo_profile' => $opposing->photo_profile,
                        'photo_profile_url' => $opposing->photo_profile
                            ? Storage::url((string) $opposing->photo_profile)
                            : null,
                    ],
                    'nominal' => (int) $consultation->session_fee,
                    'transcript' => (string) ($consultation->transcript ?? ''),
                    'action_report' => (string) ($report->action_status ?? 'new'),
                ];
            })
            ->values()
            ->all();

        return ApiResponse::paginatedItems($items, $reports, 'Daftar report berhasil diambil.');
    }

    /**
     * @OA\Put(
     *   path="/consultations/reports/{reportId}/action",
     *   tags={"Consultation Management"},
     *   security={{"BearerAuth":{}}},
     *   summary="Approve or decline consultation report",
     *
     *   @OA\Parameter(name="reportId", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       required={"action"},
     *
     *       @OA\Property(property="action", type="string", enum={"approved","declined"})
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Report action updated",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "Action report berhasil diperbarui.",
     *         "data": {
     *           "id": "01J3REPORT0001",
     *           "action_report": "approved",
     *           "actioned_at": "2026-04-27T12:30:00+00:00"
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
    public function updateReportAction(
        ConsultationReportActionRequest $request,
        string $reportId
    ): JsonResponse {
        $report = ConsultationReport::findOrFail($reportId);
        $action = $request->validated('action');

        $report->action_status = $action;
        $report->actioned_by = (string) $request->user()->getKey();
        $report->actioned_at = now();
        $report->save();

        $actionedAt = $report->actioned_at;
        $actionedAtIso = is_string($actionedAt) ? Carbon::parse($actionedAt)->toIso8601String() : null;

        return ApiResponse::success([
            'id' => (string) $report->getKey(),
            'action_report' => (string) $report->action_status,
            'actioned_at' => $actionedAtIso,
        ], 'Action report berhasil diperbarui.');
    }

    /**
     * @OA\Get(
     *   path="/consultations/payroll/summary",
     *   tags={"Consultation Management"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get pending payout summary",
     *
     *   @OA\Response(
     *     response=200,
     *     description="Payroll summary retrieved",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "Operation successful.",
     *         "data": {"pending_payouts": 8400000}
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
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

    /**
     * @OA\Get(
     *   path="/consultations/payroll/queue",
     *   tags={"Consultation Management"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get payout queue grouped by architect",
     *
     *   @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"pending","released"})),
     *   @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100)),
     *   @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Payout queue retrieved",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "Payout queue berhasil diambil.",
     *         "data": {
     *           "data": {
     *             {
     *               "architect_id": "01J3ARCH001",
     *               "architect_name": "Dimas Arsitek",
     *               "total_earnings": 900000,
     *               "per_session_earning": 300000,
     *               "total_consultation": 3,
     *               "queue_status": "pending"
     *             }
     *           },
     *           "meta": {"current_page": 1, "last_page": 1, "per_page": 15, "total": 1}
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
    public function payrollQueue(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:pending,released'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 15);
        $status = (string) ($validated['status'] ?? 'pending');

        $consultations = Consultation::query()
            ->with('architect')
            ->where('status', 'completed')
            ->where('payout_status', $status)
            ->get();

        /** @var Collection<int, Collection<int, Consultation>> $grouped */
        $grouped = $consultations->groupBy('architect_id');
        $queueRows = $grouped->map(function (Collection $items, $architectId) use ($status): array {
            /** @var Consultation $first */
            $first = $items->first();
            $totalConsultation = $items->count();
            $totalEarnings = (int) $items->sum('session_fee');
            $perSession = $totalConsultation > 0 ? (int) round($totalEarnings / $totalConsultation) : 0;

            return [
                'architect_id' => (string) $architectId,
                'architect_name' => (string) $first->architect->name,
                'total_earnings' => $totalEarnings,
                'per_session_earning' => $perSession,
                'total_consultation' => $totalConsultation,
                'queue_status' => $status,
            ];
        })->values();

        $page = max(1, (int) $request->input('page', 1));
        $total = $queueRows->count();
        $items = $queueRows->forPage($page, $perPage)->values()->all();

        return ApiResponse::success([
            'data' => $items,
            'meta' => [
                'current_page' => $page,
                'last_page' => max(1, (int) ceil($total / $perPage)),
                'per_page' => $perPage,
                'total' => $total,
            ],
        ], 'Payout queue berhasil diambil.');
    }

    /**
     * @OA\Get(
     *   path="/consultations/payroll/queue/{architectId}",
     *   tags={"Consultation Management"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get release payment detail for architect",
     *
     *   @OA\Parameter(name="architectId", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Release detail retrieved",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "Detail release payment berhasil diambil.",
     *         "data": {
     *           "architect_id": "01J3ARCH001",
     *           "release_payment_items": {
     *             {
     *               "consultation_id": "01J3CONS001",
     *               "user": {"id": "01J3USER001", "name": "Ayu Pratama"},
     *               "date": "2026-04-26T10:30:00+00:00",
     *               "fee_per_session": 300000,
     *               "verification_status": "verified"
     *             }
     *           },
     *           "payment_summary": {
     *             "consultation_per_session": 300000,
     *             "total_user_consultation": 1
     *           },
     *           "total_amount": 300000
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
    public function payrollReleaseDetail(string $architectId): JsonResponse
    {
        $consultations = Consultation::query()
            ->with('user')
            ->where('architect_id', $architectId)
            ->where('status', 'completed')
            ->where('payout_status', 'pending')
            ->orderBy('consultation_date', 'desc')
            ->get();

        $detailRows = $consultations->map(function (Consultation $consultation): array {
            $consultationDateIso = Carbon::parse($consultation->consultation_date)->toIso8601String();

            return [
                'consultation_id' => (string) $consultation->getKey(),
                'user' => [
                    'id' => (string) $consultation->user->getKey(),
                    'name' => (string) $consultation->user->name,
                ],
                'date' => $consultationDateIso,
                'fee_per_session' => (int) $consultation->session_fee,
                'verification_status' => (string) ($consultation->verification_status ?? 'unverified'),
            ];
        })->values();

        $totalConsultation = $consultations->count();
        $totalAmount = (int) $consultations->sum('session_fee');
        $perSession = $totalConsultation > 0 ? (int) round($totalAmount / $totalConsultation) : 0;

        return ApiResponse::success([
            'architect_id' => $architectId,
            'release_payment_items' => $detailRows,
            'payment_summary' => [
                'consultation_per_session' => $perSession,
                'total_user_consultation' => $totalConsultation,
            ],
            'total_amount' => $totalAmount,
        ], 'Detail release payment berhasil diambil.');
    }

    /**
     * @OA\Post(
     *   path="/consultations/payroll/queue/{architectId}/release",
     *   tags={"Consultation Management"},
     *   security={{"BearerAuth":{}}},
     *   summary="Release architect payroll",
     *
     *   @OA\Parameter(name="architectId", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\RequestBody(
     *     required=false,
     *
     *     @OA\JsonContent(
     *
     *       @OA\Property(property="consultation_ids", type="array", @OA\Items(type="string"))
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Payroll released",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "Release payment berhasil diproses.",
     *         "data": {
     *           "architect_id": "01J3ARCH001",
     *           "release_status": "selesai",
     *           "released_consultation_ids": {"01J3CONS001", "01J3CONS002"},
     *           "released_count": 2,
     *           "released_total_amount": 600000
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
    public function releasePayroll(
        PayrollReleaseRequest $request,
        string $architectId
    ): JsonResponse {
        $consultationIds = $request->validated('consultation_ids') ?? [];

        $query = Consultation::query()
            ->where('architect_id', $architectId)
            ->where('status', 'completed')
            ->where('payout_status', 'pending')
            ->where('verification_status', 'verified');

        if (is_array($consultationIds) && $consultationIds !== []) {
            $query->whereIn('id', array_map('strval', $consultationIds));
        }

        $consultations = $query->get();
        if ($consultations->isEmpty()) {
            return ApiResponse::notFound('Tidak ada konsultasi verified yang siap dibayarkan.');
        }

        $releasedIds = [];
        foreach ($consultations as $consultation) {
            $consultation->payout_status = 'released';
            $consultation->payout_released_at = now();
            $consultation->save();
            $releasedIds[] = (string) $consultation->getKey();
        }

        return ApiResponse::success([
            'architect_id' => $architectId,
            'release_status' => 'selesai',
            'released_consultation_ids' => $releasedIds,
            'released_count' => count($releasedIds),
            'released_total_amount' => (int) $consultations->sum('session_fee'),
        ], 'Release payment berhasil diproses.');
    }
}
