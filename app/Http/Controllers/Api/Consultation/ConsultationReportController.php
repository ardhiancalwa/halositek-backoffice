<?php

namespace App\Http\Controllers\Api\Consultation;

use App\Actions\Consultation\BuildConsultationReportPayloadAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Consultation\StoreConsultationReportRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Consultation;
use App\Models\ConsultationReport;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class ConsultationReportController extends Controller
{
    /**
     * @OA\Get(
     *   path="/consultations/{consultationId}/reports",
     *   tags={"Consultation Report"},
     *   security={{"BearerAuth":{}}},
     *   summary="List reports for a consultation",
     *
     *   @OA\Parameter(name="consultationId", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=50)),
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
     *           "first_page_url": "http://localhost:8000/api/v1/consultations/01J3CONS001/reports?page=1",
     *           "last_page_url": "http://localhost:8000/api/v1/consultations/01J3CONS001/reports?page=1",
     *           "next_page_url": null,
     *           "prev_page_url": null
     *         }
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError")
     * )
     */
    public function index(
        Request $request,
        string $consultationId,
        BuildConsultationReportPayloadAction $payloadBuilder
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();
        $consultation = Consultation::findOrFail($consultationId);

        if (! $this->isConsultationParticipant($user, $consultation)) {
            return ApiResponse::forbidden('Anda tidak memiliki akses ke konsultasi ini.');
        }

        $perPage = min(50, max(1, (int) $request->input('per_page', 15)));
        $reports = ConsultationReport::query()
            ->with(['consultation', 'requester', 'opposingParty', 'consultation.payment'])
            ->where('consultation_id', (string) $consultation->getKey())
            ->latest()
            ->paginate($perPage);

        $items = $reports->getCollection()
            ->map(fn (ConsultationReport $report): array => $payloadBuilder->execute($report))
            ->values()
            ->all();

        return ApiResponse::paginatedItems($items, $reports, 'Daftar report berhasil diambil.');
    }

    /**
     * @OA\Post(
     *   path="/consultations/{consultationId}/reports",
     *   tags={"Consultation Report"},
     *   security={{"BearerAuth":{}}},
     *   summary="Create consultation report",
     *
     *   @OA\Parameter(name="consultationId", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       required={"reason"},
     *
     *       @OA\Property(property="reason", type="string", example="Arsitek tidak hadir pada jadwal konsultasi.")
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=201,
     *     description="Report created",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 201,
     *         "message": "Report berhasil dibuat.",
     *         "data": {
     *           "id": "01J3REPORT0001",
     *           "requester": {
     *             "id": "01J3USER001",
     *             "name": "Ayu Pratama",
     *             "role": "user",
     *             "photo_profile": "users/profiles/ayu.webp",
     *             "photo_profile_url": "http://localhost:8000/storage/users/profiles/ayu.webp"
     *           },
     *           "reason": "Arsitek tidak hadir pada jadwal konsultasi.",
     *           "consultation_date": "2026-04-27T10:30:00+00:00",
     *           "opposing_party": {
     *             "id": "01J3ARCH001",
     *             "name": "Dimas Arsitek",
     *             "photo_profile": "users/profiles/dimas.webp",
     *             "photo_profile_url": "http://localhost:8000/storage/users/profiles/dimas.webp"
     *           },
     *           "nominal": 300000,
     *           "transcript": "Riwayat chat konsultasi...",
     *           "action_report": "new"
     *         }
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError")
     * )
     */
    public function store(
        StoreConsultationReportRequest $request,
        string $consultationId,
        BuildConsultationReportPayloadAction $payloadBuilder
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();
        $consultation = Consultation::findOrFail($consultationId);

        $requesterRole = $this->resolveRequesterRole($user, $consultation);
        if ($requesterRole === null) {
            return ApiResponse::forbidden('Anda tidak memiliki akses ke konsultasi ini.');
        }

        $opposingPartyId = $requesterRole === 'user'
            ? (string) $consultation->architect_id
            : (string) $consultation->user_id;

        $report = ConsultationReport::create([
            'consultation_id' => (string) $consultation->getKey(),
            'requester_id' => (string) $user->getKey(),
            'opposing_party_id' => $opposingPartyId,
            'requester_role' => $requesterRole,
            'reason' => (string) $request->validated('reason'),
            'action_status' => 'new',
        ]);

        return ApiResponse::created(
            $payloadBuilder->execute($report),
            'Report berhasil dibuat.',
        );
    }

    /**
     * @OA\Get(
     *   path="/consultations/reports/{reportId}",
     *   tags={"Consultation Report"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get report detail",
     *
     *   @OA\Parameter(name="reportId", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Report retrieved",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "Detail report berhasil diambil.",
     *         "data": {
     *           "id": "01J3REPORT0001",
     *           "requester": {
     *             "id": "01J3USER001",
     *             "name": "Ayu Pratama",
     *             "role": "user",
     *             "photo_profile": "users/profiles/ayu.webp",
     *             "photo_profile_url": "http://localhost:8000/storage/users/profiles/ayu.webp"
     *           },
     *           "reason": "Arsitek tidak hadir pada jadwal konsultasi.",
     *           "consultation_date": "2026-04-27T10:30:00+00:00",
     *           "opposing_party": {
     *             "id": "01J3ARCH001",
     *             "name": "Dimas Arsitek",
     *             "photo_profile": "users/profiles/dimas.webp",
     *             "photo_profile_url": "http://localhost:8000/storage/users/profiles/dimas.webp"
     *           },
     *           "nominal": 300000,
     *           "transcript": "Riwayat chat konsultasi...",
     *           "action_report": "new"
     *         }
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError")
     * )
     */
    public function show(
        Request $request,
        string $reportId,
        BuildConsultationReportPayloadAction $payloadBuilder
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();
        $report = ConsultationReport::query()
            ->with(['consultation', 'requester', 'opposingParty', 'consultation.payment'])
            ->findOrFail($reportId);

        if (! $this->isReportParticipant($user, $report)) {
            return ApiResponse::forbidden('Anda tidak memiliki akses ke report ini.');
        }

        return ApiResponse::success(
            $payloadBuilder->execute($report),
            'Detail report berhasil diambil.',
        );
    }

    private function resolveRequesterRole(User $user, Consultation $consultation): ?string
    {
        $userId = (string) $user->getKey();
        if ($userId === (string) $consultation->user_id) {
            return 'user';
        }

        if ($userId === (string) $consultation->architect_id) {
            return 'architect';
        }

        return null;
    }

    private function isConsultationParticipant(User $user, Consultation $consultation): bool
    {
        return $this->resolveRequesterRole($user, $consultation) !== null;
    }

    private function isReportParticipant(User $user, ConsultationReport $report): bool
    {
        $userId = (string) $user->getKey();

        return $userId === (string) $report->requester_id
            || $userId === (string) $report->opposing_party_id;
    }
}
