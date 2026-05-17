<?php

namespace App\Jobs;

use App\Actions\Consultation\RecordPaymentHistoryAction;
use App\Models\Consultation;
use App\Models\ConsultationReport;
use App\Models\Payment;
use App\Models\Refund;
use App\Services\MidtransService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessConsultationRefundJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = [300, 600, 1200, 2400]; // 5m, 10m, 20m, 40m

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $paymentId,
        protected string $reportId,
        protected string $refundId
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(MidtransService $midtrans, RecordPaymentHistoryAction $recordPaymentHistory): void
    {
        $payment = Payment::find($this->paymentId);
        $report = ConsultationReport::find($this->reportId);
        $refund = Refund::find($this->refundId);

        if (! $payment || ! $report) {
            return;
        }

        if ($refund) {
            if (! $refund->midtrans_refund_key) {
                $refund->midtrans_refund_key = 'refund-' . Str::lower(Str::random(10));
            }
            $refund->status = 'processing';
            $refund->save();
        }

        $payment->refund_status = 'processing';
        $payment->save();

        try {
            $refundReason = $refund ? $refund->reason : 'Buyback consultation report #' . (string) $report->getKey();
            $refundKey = $refund ? $refund->midtrans_refund_key : null;

            $refundResponse = $midtrans->refundTransaction(
                (string) $payment->order_id,
                (int) $payment->amount,
                $refundReason,
                $refundKey
            );

            $statusCode = $refundResponse['status_code'] ?? null;
            $statusCodeStr = is_int($statusCode) ? (string) $statusCode : (is_string($statusCode) ? $statusCode : '');

            if (str_starts_with($statusCodeStr, '2')) {
                $payment->status = 'completed'; // Keep payment status as completed
                $payment->refund_status = 'refunded';
                $payment->midtrans_response = array_merge(
                    is_array($payment->midtrans_response) ? $payment->midtrans_response : [],
                    ['refund' => $refundResponse]
                );
                $payment->save();

                if ($refund) {
                    $refund->status = 'refunded';
                    $refund->midtrans_response = $refundResponse;
                    $refund->processed_at = now();
                    $refund->save();
                }

                $consultation = $report->consultation;
                if ($consultation instanceof Consultation) {
                    $consultation->payout_status = 'refunded';
                    $consultation->save();
                }

                $recordPaymentHistory->execute(
                    $payment,
                    'buyback_refund_applied',
                    'consultation_refund_job',
                    ['refund_response' => $refundResponse],
                    'Refund Midtrans berhasil diproses via antrian.'
                );
            } else {
                $statusMessage = $refundResponse['status_message'] ?? 'Unknown error';

                // Check if it's a "try again later" error (like 418)
                if (str_contains(strtolower($statusMessage), 'within this time') || $statusCodeStr === '418') {
                    Log::info("Midtrans refund delayed for Order {$payment->order_id}: {$statusMessage}. Retrying...");
                    throw new \Exception("Midtrans refund delayed: {$statusMessage}");
                }

                $payment->refund_status = 'failed';
                $payment->save();

                if ($refund) {
                    $refund->status = 'failed';
                    $refund->midtrans_response = $refundResponse;
                    $refund->error_message = $statusMessage;
                    $refund->save();
                }

                $recordPaymentHistory->execute(
                    $payment,
                    'buyback_refund_failed',
                    'consultation_refund_job',
                    ['refund_response' => $refundResponse],
                    'Refund Midtrans gagal: ' . $statusMessage
                );
            }
        } catch (RequestException | ConnectionException | \Exception $exception) {
            $isRetryable = false;
            $response = ($exception instanceof RequestException) ? $exception->response : null;

            if ($response) {
                $status = $response->status();
                $message = $response->json('status_message') ?? '';
                if ($status === 418 || str_contains(strtolower($message), 'within this time')) {
                    $isRetryable = true;
                }
            } elseif ($exception->getMessage() && str_contains(strtolower($exception->getMessage()), 'delayed')) {
                $isRetryable = true;
            }

            if ($isRetryable && $this->attempts() < $this->tries) {
                // Let it fail and retry based on $backoff
                throw $exception;
            }

            $payment->refund_status = 'failed';
            $payment->save();

            if ($refund) {
                $refund->status = 'failed';
                $refund->error_message = $exception->getMessage();
                if ($response) {
                    $refund->midtrans_response = $response->json();
                }
                $refund->save();
            }

            $recordPaymentHistory->execute(
                $payment,
                'buyback_refund_failed',
                'consultation_refund_job',
                [
                    'error' => $exception->getMessage(),
                    'response' => $response ? $response->json() : null,
                ],
                'Refund Midtrans gagal setelah beberapa percobaan: ' . $exception->getMessage()
            );
        }
    }
}
