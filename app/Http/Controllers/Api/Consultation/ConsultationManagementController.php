<?php

namespace App\Http\Controllers\Api\Consultation;

use App\Actions\Consultation\BuildConsultationReportPayloadAction;
use App\Actions\Consultation\RecordPaymentHistoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Consultation\ConsultationReportActionRequest;
use App\Http\Requests\Api\Consultation\ConsultationReportFilterRequest;
use App\Http\Requests\Api\Consultation\PayrollReleaseRequest;
use App\Http\Responses\ApiResponse;
use App\Jobs\ProcessConsultationRefundJob;
use App\Models\Consultation;
use App\Models\ConsultationReport;
use App\Models\Payment;
use App\Models\Refund;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use OpenApi\Annotations as OA;

class ConsultationManagementController extends Controller
{
    private const ARCHITECT_RELEASE_TAX_PERCENT = 10;

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
     *   @OA\Response(response=500, ref="#/components/responses/ServerError"),
     *   @OA\Response(response=503, ref="#/components/responses/ServiceUnavailableError")
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
     *   @OA\Response(response=500, ref="#/components/responses/ServerError"),
     *   @OA\Response(response=503, ref="#/components/responses/ServiceUnavailableError")
     * )
     */
    public function reportList(
        ConsultationReportFilterRequest $request,
        BuildConsultationReportPayloadAction $payloadBuilder
    ): JsonResponse {
        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? 15);
        $role = $validated['role'] ?? null;

        $query = ConsultationReport::query()
            ->with(['consultation', 'requester', 'opposingParty', 'consultation.payment'])
            ->latest();

        if (is_string($role) && $role !== '') {
            $query->where('requester_role', $role);
        }

        $reports = $query->paginate($perPage);

        $items = $reports->getCollection()
            ->map(fn (ConsultationReport $report): array => $payloadBuilder->execute($report))
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
     *   description="Menerapkan keputusan report sekaligus rule buyback: report oleh user + approved => refund user; report oleh architect + declined => refund user; selain itu payment tidak berubah.",
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
     *           "actioned_at": "2026-04-27T12:30:00+00:00",
     *           "buyback_applied": true,
     *           "payment_status": "refunded"
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
        string $reportId,
        RecordPaymentHistoryAction $recordPaymentHistory,
        MidtransService $midtrans
    ): JsonResponse {
        $report = ConsultationReport::findOrFail($reportId);
        $action = $request->validated('action');

        $report->action_status = $action;
        $report->actioned_by = (string) $request->user()->getKey();
        $report->actioned_at = now();
        $report->save();

        $requesterRole = (string) ($report->requester_role ?? '');
        $shouldApplyBuyback = (
            ($requesterRole === 'user' && $action === 'approved')
            || ($requesterRole === 'architect' && $action === 'declined')
        );

        $buybackApplied = false;
        $paymentStatus = null;
        $refundStatus = null;

        if ($shouldApplyBuyback) {
            $payment = Payment::query()
                ->where('consultation_id', (string) $report->consultation_id)
                ->first();

            if (! ($payment instanceof Payment)) {
                return ApiResponse::validationError([
                    'consultation_id' => ['Payment konsultasi tidak ditemukan untuk proses buyback.'],
                ]);
            }

            if ($payment->refund_status === 'refunded') {
                $buybackApplied = true;
                $paymentStatus = (string) $payment->status;
                $refundStatus = (string) $payment->refund_status;

                $actionedAt = $report->actioned_at;
                $actionedAtIso = $actionedAt->toIso8601String();

                return ApiResponse::success([
                    'id' => (string) $report->getKey(),
                    'action_report' => (string) $report->action_status,
                    'actioned_at' => $actionedAtIso,
                    'buyback_applied' => $buybackApplied,
                    'payment_status' => $paymentStatus,
                    'refund_status' => $refundStatus,
                ], 'Action report berhasil diperbarui.');
            }

            // Create refund record
            $refund = Refund::create([
                'payment_id' => (string) $payment->getKey(),
                'order_id' => (string) $payment->order_id,
                'report_id' => (string) $report->getKey(),
                'amount' => (int) $payment->amount,
                'reason' => 'Buyback consultation report #' . (string) $report->getKey(),
                'status' => 'approved',
            ]);

            // Update refund status and dispatch job
            $payment->refund_status = 'approved';
            $payment->save();

            $recordPaymentHistory->execute(
                $payment,
                'buyback_refund_approved',
                'consultation_report_action',
                [
                    'report_id' => (string) $report->getKey(),
                    'refund_id' => (string) $refund->getKey(),
                    'requester_role' => $requesterRole,
                    'action' => $action,
                    'order_id' => (string) $payment->order_id,
                    'amount' => (int) $payment->amount,
                ],
                'Admin menyetujui refund buyback. Memulai proses refund async.'
            );

            ProcessConsultationRefundJob::dispatch(
                (string) $payment->getKey(),
                (string) $report->getKey(),
                (string) $refund->getKey()
            );

            $buybackApplied = true;
            $paymentStatus = (string) $payment->status;
            $refundStatus = (string) $payment->refund_status;
        }

        $actionedAt = $report->actioned_at;
        $actionedAtIso = $actionedAt->toIso8601String();

        return ApiResponse::success([
            'id' => (string) $report->getKey(),
            'action_report' => (string) $report->action_status,
            'actioned_at' => $actionedAtIso,
            'buyback_applied' => $buybackApplied,
            'payment_status' => $paymentStatus,
            'refund_status' => $refundStatus,
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
        $pendingConsultations = Consultation::query()
            ->where('status', 'completed')
            ->where('payout_status', 'pending')
            ->get();

        $pendingGross = (int) $pendingConsultations->sum('session_fee');
        $pendingTax = (int) $pendingConsultations->sum(fn (Consultation $consultation): int => $this->resolvePayoutTaxAmount($consultation));
        $pendingAmount = (int) $pendingConsultations->sum(fn (Consultation $consultation): int => $this->resolvePayoutAmount($consultation));

        return ApiResponse::success([
            'pending_payouts_gross' => $pendingGross,
            'pending_payouts_tax' => $pendingTax,
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
            $totalGrossEarnings = (int) $items->sum('session_fee');
            $totalTax = (int) $items->sum(fn (Consultation $consultation): int => $this->resolvePayoutTaxAmount($consultation));
            $totalEarnings = (int) $items->sum(fn (Consultation $consultation): int => $this->resolvePayoutAmount($consultation));
            $perSession = $totalConsultation > 0 ? (int) round($totalEarnings / $totalConsultation) : 0;

            return [
                'architect_id' => (string) $architectId,
                'architect_name' => (string) $first->architect->name,
                'total_gross_earnings' => $totalGrossEarnings,
                'total_tax' => $totalTax,
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
                'fee_per_session' => $this->resolvePayoutAmount($consultation),
                'gross_fee_per_session' => (int) $consultation->session_fee,
                'tax_per_session' => $this->resolvePayoutTaxAmount($consultation),
                'verification_status' => (string) ($consultation->verification_status ?? 'unverified'),
            ];
        })->values();

        $totalConsultation = $consultations->count();
        $totalGrossAmount = (int) $consultations->sum('session_fee');
        $totalTaxAmount = (int) $consultations->sum(fn (Consultation $consultation): int => $this->resolvePayoutTaxAmount($consultation));
        $totalAmount = (int) $consultations->sum(fn (Consultation $consultation): int => $this->resolvePayoutAmount($consultation));
        $perSession = $totalConsultation > 0 ? (int) round($totalAmount / $totalConsultation) : 0;

        return ApiResponse::success([
            'architect_id' => $architectId,
            'release_payment_items' => $detailRows,
            'payment_summary' => [
                'consultation_per_session' => $perSession,
                'total_user_consultation' => $totalConsultation,
            ],
            'total_gross_amount' => $totalGrossAmount,
            'total_tax_amount' => $totalTaxAmount,
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
        $releasedGrossTotal = 0;
        $releasedTaxTotal = 0;
        $releasedNetTotal = 0;
        foreach ($consultations as $consultation) {
            $grossAmount = (int) $consultation->session_fee;
            $taxAmount = $this->resolvePayoutTaxAmount($consultation);
            $netAmount = $this->resolvePayoutAmount($consultation);
            $consultation->payout_status = 'released';
            $consultation->payout_released_at = now();
            $consultation->payout_tax_amount = $taxAmount;
            $consultation->payout_amount = $netAmount;
            $consultation->save();
            $releasedIds[] = (string) $consultation->getKey();
            $releasedGrossTotal += $grossAmount;
            $releasedTaxTotal += $taxAmount;
            $releasedNetTotal += $netAmount;
        }

        return ApiResponse::success([
            'architect_id' => $architectId,
            'release_status' => 'selesai',
            'released_consultation_ids' => $releasedIds,
            'released_count' => count($releasedIds),
            'released_gross_total_amount' => $releasedGrossTotal,
            'released_total_tax' => $releasedTaxTotal,
            'released_total_amount' => $releasedNetTotal,
        ], 'Release payment berhasil diproses.');
    }

    private function resolvePayoutTaxAmount(Consultation $consultation): int
    {
        $storedTaxAmount = $consultation->payout_tax_amount;
        if (is_numeric($storedTaxAmount)) {
            return (int) $storedTaxAmount;
        }

        return (int) round(((int) $consultation->session_fee) * self::ARCHITECT_RELEASE_TAX_PERCENT / 100);
    }

    private function resolvePayoutAmount(Consultation $consultation): int
    {
        $storedPayoutAmount = $consultation->payout_amount;
        if (is_numeric($storedPayoutAmount)) {
            return (int) $storedPayoutAmount;
        }

        return max(0, (int) $consultation->session_fee - $this->resolvePayoutTaxAmount($consultation));
    }
}
