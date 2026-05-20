<?php

namespace App\Http\Controllers\Api\Consultation;

use App\Actions\Consultation\FinalizeConsultationPaymentAction;
use App\Actions\Consultation\RecordPaymentHistoryAction;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Payment;
use App\Models\Refund;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

class PaymentWebhookController extends Controller
{
    /**
     * @OA\Post(
     *   path="/webhooks/midtrans",
     *   tags={"Consultation Payment"},
     *   summary="Handle Midtrans webhook (primary status updater)",
     *   description="Endpoint utama (source of truth) untuk update status pembayaran dari notifikasi Midtrans. Konfigurasi webhook Midtrans diarahkan ke /api/v1/webhooks/midtrans. Pada environment local/testing, signature_key otomatis digenerate bila kosong atau bernilai 'string' untuk kebutuhan test manual.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       required={"order_id","status_code","transaction_status","gross_amount"},
     *
     *       @OA\Property(property="order_id", type="string", example="CONS-20260515231400-ABCD1234"),
     *       @OA\Property(property="status_code", type="string", example="200"),
     *       @OA\Property(property="transaction_status", type="string", example="settlement"),
     *       @OA\Property(property="transaction_id", type="string", example="trx-123"),
     *       @OA\Property(property="gross_amount", type="string", example="150000.00"),
     *       @OA\Property(property="payment_type", type="string", example="bank_transfer"),
     *       @OA\Property(property="signature_key", type="string", nullable=true, description="Wajib di production. Di local/testing boleh kosong atau 'string' untuk auto-generate.")
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Webhook pembayaran berhasil diproses",
     *
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "status_code": 200,
     *         "message": "Webhook pembayaran berhasil diproses.",
     *         "data": {
     *           "payment_id": "01J3PAYMENT001",
     *           "order_id": "CONS-20260515231400-ABCD1234",
     *           "status": "completed",
     *           "consultation_id": "01J3CONS001",
     *           "conversation_id": "01J3CONV001"
     *         }
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(response=403, ref="#/components/responses/ForbiddenError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError")
     * )
     */
    public function handleMidtrans(
        Request $request,
        MidtransService $midtrans,
        FinalizeConsultationPaymentAction $finalizePayment,
        RecordPaymentHistoryAction $recordHistory
    ): JsonResponse {
        /** @var array<string, mixed> $payload */
        $payload = $request->all();

        $signature = (string) ($payload['signature_key'] ?? '');
        $isLocalOrTesting = app()->environment(['local', 'testing']);
        if ($isLocalOrTesting && ($signature === '' || $signature === 'string')) {
            $orderId = (string) ($payload['order_id'] ?? '');
            $statusCode = (string) ($payload['status_code'] ?? '');
            $grossAmount = (string) ($payload['gross_amount'] ?? '');

            if ($orderId !== '' && $statusCode !== '' && $grossAmount !== '') {
                $payload['signature_key'] = $midtrans->generateSignatureKey($orderId, $statusCode, $grossAmount);
            }
        }

        if (! $midtrans->isValidSignature($payload)) {
            return ApiResponse::forbidden('Signature Midtrans tidak valid.');
        }

        $orderId = (string) ($payload['order_id'] ?? '');
        if ($orderId === '') {
            return ApiResponse::validationError([
                'order_id' => ['order_id wajib dikirim pada webhook Midtrans.'],
            ]);
        }

        $payment = Payment::query()->where('order_id', $orderId)->first();
        if (! $payment instanceof Payment) {
            return ApiResponse::success(message: 'Order tidak ditemukan, webhook diabaikan.');
        }

        $payment->status = $midtrans->mapPaymentStatus($payload);
        $payment->transaction_id = is_string($payload['transaction_id'] ?? null)
            ? (string) $payload['transaction_id']
            : $payment->transaction_id;
        $payment->payment_method = is_string($payload['payment_type'] ?? null)
            ? (string) $payload['payment_type']
            : $payment->payment_method;
        $payment->midtrans_response = $payload;

        $transactionStatus = strtolower((string) ($payload['transaction_status'] ?? ''));
        if ($transactionStatus === 'refund' || $transactionStatus === 'partial_refund') {
            $payment->refund_status = $transactionStatus === 'refund' ? 'refunded' : 'partial_refunded';

            if (isset($payload['refunds']) && is_array($payload['refunds'])) {
                foreach ($payload['refunds'] as $refundItem) {
                    $refundKey = $refundItem['refund_key'] ?? null;
                    if ($refundKey) {
                        $refundModel = Refund::where('midtrans_refund_key', $refundKey)->first();
                        if ($refundModel) {
                            $refundModel->status = $transactionStatus === 'refund' ? 'refunded' : 'partial_refunded';
                            $refundModel->midtrans_response = $payload;
                            $refundModel->processed_at = now();
                            $refundModel->save();
                        }
                    }
                }
            } else {
                $refundModel = Refund::where('payment_id', (string) $payment->getKey())
                    ->whereIn('status', ['approved', 'processing'])
                    ->first();
                if ($refundModel) {
                    $refundModel->status = $transactionStatus === 'refund' ? 'refunded' : 'partial_refunded';
                    $refundModel->midtrans_response = $payload;
                    $refundModel->processed_at = now();
                    $refundModel->save();
                }
            }
        }

        if ($payment->status === 'completed' && ! $payment->paid_at) {
            $settlementTime = is_string($payload['settlement_time'] ?? null)
                ? Carbon::parse((string) $payload['settlement_time'])
                : now();
            $payment->paid_at = $settlementTime;
        }

        $payment->save();
        $recordHistory->execute($payment, 'payment_webhook_received', 'webhook', $payload);
        $payment = $finalizePayment->execute($payment);

        return ApiResponse::success([
            'payment_id' => (string) $payment->getKey(),
            'order_id' => (string) $payment->order_id,
            'status' => (string) $payment->status,
            'consultation_id' => $payment->consultation_id,
            'conversation_id' => $payment->conversation_id,
        ], 'Webhook pembayaran berhasil diproses.');
    }
}
