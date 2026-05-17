<?php

namespace App\Actions\Consultation;

use App\Models\Payment;
use App\Models\PaymentHistory;

final class RecordPaymentHistoryAction
{
    /**
     * @param  array<string, mixed>|null  $payload
     */
    public function execute(
        Payment $payment,
        string $event,
        string $source,
        ?array $payload = null,
        ?string $notes = null
    ): PaymentHistory {
        return PaymentHistory::create([
            'payment_id' => (string) $payment->getKey(),
            'order_id' => (string) $payment->order_id,
            'user_id' => (string) $payment->user_id,
            'architect_id' => (string) $payment->architect_id,
            'status' => (string) $payment->status,
            'event' => $event,
            'source' => $source,
            'notes' => $notes,
            'midtrans_response' => $payload,
            'occurred_at' => now(),
        ]);
    }
}
