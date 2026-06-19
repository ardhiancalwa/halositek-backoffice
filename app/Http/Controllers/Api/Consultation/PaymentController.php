<?php

namespace App\Http\Controllers\Api\Consultation;

use App\Actions\Consultation\FinalizeConsultationPaymentAction;
use App\Actions\Consultation\RecordPaymentHistoryAction;
use App\Enums\ApiStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Consultation\InitiateConsultationPaymentRequest;
use App\Http\Responses\ApiResponse;
use App\Models\ArchitectProfile;
use App\Models\Consultation;
use App\Models\Payment;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

class PaymentController extends Controller
{
    private const USER_CONSULTATION_TAX_PERCENT = 10;

    /**
     * @OA\Post(
     *   path="/consultations/payments/initiate",
     *   tags={"Consultation Payment"},
     *   security={{"BearerAuth":{}}},
     *   summary="Initiate consultation payment",
     *   description="Membuat transaksi Midtrans Snap untuk pembayaran konsultasi.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       required={"architect_id"},
     *
     *       @OA\Property(property="architect_id", type="string", example="01J3ARCHITECT001")
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=201,
     *     description="Pembayaran berhasil dibuat",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 201,
     *         "message": "Pembayaran konsultasi berhasil dibuat.",
     *         "data": {
     *           "payment_id": "01J3PAYMENT001",
     *           "order_id": "CONS-20260515231400-ABCD1234",
     *           "status": "pending",
     *           "snap_token": "snap-token-123",
     *           "redirect_url": "https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123",
     *           "consultation_details": {
     *             "architect_id": "01J3ARCHITECT001",
     *             "architect_name": "Arsitek A",
     *             "architect_photo": "https://...",
     *             "duration_hours": 2,
     *             "amount": 150000
     *           }
     *         }
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError"),
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=503, ref="#/components/responses/ServiceUnavailableError")
     * )
     */
    public function initiate(
        InitiateConsultationPaymentRequest $request,
        MidtransService $midtrans,
        RecordPaymentHistoryAction $recordHistory
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();
        if (! $user->isUser()) {
            return ApiResponse::forbidden('Hanya user yang dapat membuat pembayaran konsultasi.');
        }

        $architect = User::query()->findOrFail((string) $request->validated('architect_id'));
        if (! $architect->isArchitect()) {
            return ApiResponse::validationError([
                'architect_id' => ['User yang dipilih bukan arsitek.'],
            ]);
        }

        $activeConsultation = Consultation::query()
            ->where('user_id', (string) $user->getKey())
            ->where('architect_id', (string) $architect->getKey())
            ->orderBy('consultation_date', 'desc')
            ->get()
            ->first(static fn (Consultation $consultation): bool => $consultation->isSessionActive());
        if ($activeConsultation instanceof Consultation) {
            return ApiResponse::validationError([
                'architect_id' => ['Sesi konsultasi dengan arsitek ini masih berjalan. Tunggu sesi selesai untuk membuat sesi baru.'],
            ]);
        }

        $architectProfile = $architect->architectProfile;
        $durationHours = $architectProfile instanceof ArchitectProfile
            ? (int) ($architectProfile->consultation_duration ?? 0)
            : 0;
        $consultationAmount = $architectProfile instanceof ArchitectProfile
            ? (int) ($architectProfile->consultation_fee ?? 0)
            : 0;

        if ($durationHours <= 0 || $consultationAmount <= 0) {
            return ApiResponse::validationError([
                'architect_id' => ['Arsitek yang dipilih belum mengatur biaya atau durasi konsultasi yang valid.'],
            ]);
        }

        $taxAmount = (int) round($consultationAmount * self::USER_CONSULTATION_TAX_PERCENT / 100);
        $totalPaidAmount = $consultationAmount + $taxAmount;
        $orderId = sprintf('CONS-%s-%s', now()->format('YmdHis'), Str::upper(Str::random(8)));

        $payment = Payment::create([
            'order_id' => $orderId,
            'user_id' => (string) $user->getKey(),
            'architect_id' => (string) $architect->getKey(),
            'duration_hours' => $durationHours,
            'amount' => $consultationAmount,
            'user_tax_amount' => $taxAmount,
            'total_paid_amount' => $totalPaidAmount,
            'status' => 'pending',
        ]);
        $recordHistory->execute($payment, 'payment_initiated', 'api');

        try {
            $snap = $midtrans->createSnapTransaction([
                'order_id' => $orderId,
                'gross_amount' => $totalPaidAmount,
                'first_name' => (string) $user->name,
                'email' => (string) $user->email,
                'item_details' => [
                    [
                        'price' => $consultationAmount,
                        'quantity' => 1,
                        'name' => 'Konsultasi Arsitek',
                    ],
                    [
                        'price' => $taxAmount,
                        'quantity' => 1,
                        'name' => 'Pajak Konsultasi (10%)',
                    ],
                ],
                'metadata' => [
                    'payment_id' => (string) $payment->getKey(),
                    'architect_id' => (string) $architect->getKey(),
                    'duration_hours' => $durationHours,
                    'consultation_amount' => $consultationAmount,
                    'tax_amount' => $taxAmount,
                    'total_paid_amount' => $totalPaidAmount,
                ],
            ]);
        } catch (RequestException | ConnectionException $exception) {
            $errorBody = $exception instanceof RequestException ? $exception->response->json() : null;

            Log::channel('single')->error('Midtrans Snap Initiation Failed', [
                'order_id' => $orderId,
                'user_id' => (string) $user->getKey(),
                'error_message' => $exception->getMessage(),
                'response_body' => $errorBody,
            ]);

            $payment->status = 'failed';
            $payment->midtrans_response = [
                'error' => $exception->getMessage(),
                'body' => $exception instanceof RequestException ? $exception->response->json() : null,
            ];
            $payment->save();
            $recordHistory->execute(
                $payment,
                'payment_initiation_failed',
                'api',
                $payment->midtrans_response,
                'Gagal membuat transaksi ke Midtrans.'
            );

            return ApiResponse::error(
                'Gagal membuat transaksi Midtrans.',
                ApiStatus::SERVICE_UNAVAILABLE
            );
        }

        $payment->snap_token = is_string($snap['token'] ?? null) ? $snap['token'] : null;
        $payment->snap_redirect_url = is_string($snap['redirect_url'] ?? null) ? $snap['redirect_url'] : null;
        $payment->midtrans_response = $snap;
        $payment->save();
        $recordHistory->execute($payment, 'snap_token_created', 'api', $snap);

        return ApiResponse::created([
            'payment_id' => (string) $payment->getKey(),
            'order_id' => $payment->order_id,
            'status' => $payment->status,
            'snap_token' => $payment->snap_token,
            'redirect_url' => $payment->snap_redirect_url,
            'consultation_details' => [
                'architect_id' => (string) $architect->getKey(),
                'architect_name' => (string) $architect->name,
                'architect_photo' => (string) $architect->photo_profile_url,
                'duration_hours' => $durationHours,
                'consultation_amount' => $consultationAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalPaidAmount,
            ],
        ], 'Pembayaran konsultasi berhasil dibuat.');
    }

    /**
     * @OA\Get(
     *   path="/consultations/payments/{transactionId}/status",
     *   tags={"Consultation Payment"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get payment status (fallback polling)",
     *   description="Endpoint fallback polling untuk Web/Flutter. Status utama pembayaran diperbarui otomatis melalui webhook Midtrans. Endpoint ini akan mencoba sinkronisasi ke Midtrans bila status di database masih pending.",
     *
     *   @OA\Parameter(name="transactionId", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Status pembayaran berhasil diambil",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "Status pembayaran berhasil diambil.",
     *         "data": {
     *           "payment_id": "01J3PAYMENT001",
     *           "order_id": "CONS-20260515231400-ABCD1234",
     *           "status": "completed",
     *           "transaction_id": "trx-123",
     *           "consultation_id": "01J3CONS001",
     *           "conversation_id": "01J3CONV001",
     *           "can_enter_consultation": true,
     *           "paid_at": "2026-05-15T23:20:00+07:00"
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
    public function status(
        Request $request,
        string $transactionId,
        MidtransService $midtrans,
        FinalizeConsultationPaymentAction $finalizePayment,
        RecordPaymentHistoryAction $recordHistory
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();
        $payment = Payment::query()->where('transaction_id', $transactionId)->firstOrFail();

        $isOwner = (string) $payment->user_id === (string) $user->getKey();
        $isArchitect = (string) $payment->architect_id === (string) $user->getKey();
        if (! $isOwner && ! $isArchitect) {
            return ApiResponse::forbidden('Anda tidak memiliki akses ke pembayaran ini.');
        }

        if ($payment->status === 'pending') {
            try {
                $statusPayload = $midtrans->fetchTransactionStatus((string) $payment->order_id);
                $payment->status = $midtrans->mapPaymentStatus($statusPayload);
                $payment->transaction_id = is_string($statusPayload['transaction_id'] ?? null)
                    ? $statusPayload['transaction_id']
                    : $payment->transaction_id;
                $payment->payment_method = is_string($statusPayload['payment_type'] ?? null)
                    ? $statusPayload['payment_type']
                    : $payment->payment_method;
                $payment->midtrans_response = $statusPayload;
                if ($payment->status === 'completed' && ! $payment->paid_at) {
                    $payment->paid_at = now();
                }
                $payment->save();
                $recordHistory->execute($payment, 'payment_status_synced', 'status_endpoint', $statusPayload);
            } catch (RequestException | ConnectionException $exception) {
                Log::warning('Failed to sync payment status from Midtrans.', [
                    'payment_id' => (string) $payment->getKey(),
                    'order_id' => (string) $payment->order_id,
                    'error' => $exception->getMessage(),
                ]);
                $recordHistory->execute(
                    $payment,
                    'payment_status_sync_failed',
                    'status_endpoint',
                    ['error' => $exception->getMessage()],
                    'Gagal sinkronisasi status ke Midtrans.'
                );
            }
        }

        if ($payment->status === 'completed' && $payment->consultation_id === null) {
            $payment = $finalizePayment->execute($payment);
        }

        return ApiResponse::success([
            'payment_id' => (string) $payment->getKey(),
            'order_id' => (string) $payment->order_id,
            'status' => (string) $payment->status,
            'refund_status' => (string) ($payment->refund_status ?? 'none'),
            'transaction_id' => $payment->transaction_id,
            'consultation_id' => $payment->consultation_id,
            'conversation_id' => $payment->conversation_id,
            'can_enter_consultation' => $payment->status === 'completed' && $payment->conversation_id !== null,
            'paid_at' => $payment->paid_at?->toIso8601String(),
        ], 'Status pembayaran berhasil diambil.');
    }

    /**
     * @OA\Get(
     *   path="/consultations/{architectId}/check-status",
     *   tags={"Consultation Payment"},
     *   security={{"BearerAuth":{}}},
     *   summary="Check consultation session status with an architect",
     *   description="Mengecek status sesi konsultasi user dengan arsitek tertentu berdasarkan transaksi terakhir. Status yang mungkin: no_session, pending_payment, session_active.",
     *
     *   @OA\Parameter(name="architectId", in="path", required=true, @OA\Schema(type="string")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Status konsultasi berhasil diambil",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "Status konsultasi berhasil diambil.",
     *         "data": {
     *           "status": "session_active",
     *           "consultation_id": "01J3CONS001",
     *           "conversation_id": "01J3CONV001",
     *           "remaining_time": {"days": 0, "hours": 1, "minutes": 30, "seconds": 0}
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
    public function checkConsultationStatus(
        Request $request,
        string $architectId,
        FinalizeConsultationPaymentAction $finalizePayment
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();
        if (! $user->isUser()) {
            return ApiResponse::forbidden('Hanya user yang dapat mengecek status konsultasi.');
        }

        $architect = User::query()->find($architectId);
        if (! $architect instanceof User || ! $architect->isArchitect()) {
            return ApiResponse::notFound('Arsitek tidak ditemukan.');
        }

        $payment = Payment::query()
            ->where('user_id', (string) $user->getKey())
            ->where('architect_id', $architectId)
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $payment instanceof Payment) {
            return ApiResponse::success(
                $this->buildNoSessionData($architect),
                'Status konsultasi berhasil diambil.'
            );
        }

        if ($payment->status === 'pending') {
            return ApiResponse::success(
                $this->buildPendingPaymentData($payment),
                'Status konsultasi berhasil diambil.'
            );
        }

        if ($payment->status === 'completed' && $payment->consultation_id === null) {
            $payment = $finalizePayment->execute($payment);
        }

        $consultation = $payment->consultation_id !== null
            ? Consultation::query()->find($payment->consultation_id)
            : null;

        if (! $consultation instanceof Consultation) {
            return ApiResponse::success(
                $this->buildNoSessionData($architect),
                'Status konsultasi berhasil diambil.'
            );
        }

        if ($consultation->isSessionActive()) {
            return ApiResponse::success(
                $this->buildSessionActiveData($consultation),
                'Status konsultasi berhasil diambil.'
            );
        }

        return ApiResponse::success(
            $this->buildNoSessionData($architect),
            'Status konsultasi berhasil diambil.'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildNoSessionData(User $architect): array
    {
        $architectProfile = $architect->architectProfile;

        return [
            'status' => 'no_session',
            'architect' => [
                'id' => (string) $architect->getKey(),
                'name' => (string) $architect->name,
                'photo_profile_url' => $architect->photo_profile_url,
            ],
            'consultation_fee' => $architectProfile instanceof ArchitectProfile
                ? (int) ($architectProfile->consultation_fee ?? 0)
                : 0,
            'duration_hours' => $architectProfile instanceof ArchitectProfile
                ? (int) ($architectProfile->consultation_duration ?? 0)
                : 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPendingPaymentData(Payment $payment): array
    {
        return [
            'status' => 'pending_payment',
            'payment_id' => (string) $payment->getKey(),
            'order_id' => (string) $payment->order_id,
            'snap_token' => $payment->snap_token,
            'redirect_url' => $payment->snap_redirect_url,
            'amount' => (int) $payment->total_paid_amount,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSessionActiveData(Consultation $consultation): array
    {
        return [
            'status' => 'session_active',
            'consultation_id' => (string) $consultation->getKey(),
            'conversation_id' => $consultation->conversation_id,
            'consultation_date' => $consultation->consultation_date?->toIso8601String(),
            'remaining_time' => $consultation->remainingDuration(),
        ];
    }

    /**
     * @OA\Get(
     *   path="/consultations/payments/history",
     *   tags={"Consultation Payment"},
     *   security={{"BearerAuth":{}}},
     *   summary="Get payment history",
     *   description="Mengambil riwayat pembayaran konsultasi milik user yang login.",
     *
     *   @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=50, example=15)),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Riwayat pembayaran berhasil diambil",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "Riwayat pembayaran berhasil diambil.",
     *         "data": {
     *           {
     *             "id": "01J3PAYMENT001",
     *             "order_id": "CONS-20260515231400-ABCD1234",
     *             "status": "completed",
     *             "amount": 150000,
     *             "duration_hours": 2,
     *             "payment_method": "bank_transfer",
     *             "paid_at": "2026-05-15T23:20:00+07:00",
     *             "consultation_id": "01J3CONS001",
     *             "conversation_id": "01J3CONV001",
     *             "architect": {
     *               "id": "01J3ARCHITECT001",
     *               "name": "Arsitek A",
     *               "photo_profile_url": "https://..."
     *             }
     *           }
     *         },
     *         "meta": {"current_page": 1, "last_page": 1, "per_page": 15, "total": 1}
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(response=401, ref="#/components/responses/UnauthorizedError")
     * )
     */
    public function history(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $perPage = min(50, (int) $request->input('per_page', 15));

        $payments = Payment::query()
            ->where('user_id', (string) $user->getKey())
            ->with('architect')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $items = $payments->getCollection()->map(function (Payment $payment): array {
            return [
                'id' => (string) $payment->getKey(),
                'order_id' => (string) $payment->order_id,
                'status' => (string) $payment->status,
                'refund_status' => (string) ($payment->refund_status ?? 'none'),
                'amount' => (int) $payment->amount,
                'tax_amount' => (int) ($payment->user_tax_amount ?? round(((int) $payment->amount) * self::USER_CONSULTATION_TAX_PERCENT / 100)),
                'total_paid_amount' => (int) ($payment->total_paid_amount ?? ((int) $payment->amount + (int) ($payment->user_tax_amount ?? round(((int) $payment->amount) * self::USER_CONSULTATION_TAX_PERCENT / 100)))),
                'duration_hours' => (int) $payment->duration_hours,
                'payment_method' => $payment->payment_method,
                'paid_at' => $payment->paid_at?->toIso8601String(),
                'created_at' => $payment->created_at?->toIso8601String(),
                'consultation_id' => $payment->consultation_id,
                'conversation_id' => $payment->conversation_id,
                'architect' => [
                    'id' => (string) ($payment->architect?->getKey() ?? ''),
                    'name' => (string) ($payment->architect->name ?? ''),
                    'photo_profile_url' => $payment->architect?->photo_profile_url,
                ],
            ];
        })->all();

        return ApiResponse::paginatedItems($items, $payments, 'Riwayat pembayaran berhasil diambil.');
    }
}
