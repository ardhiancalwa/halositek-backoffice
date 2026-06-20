<?php

namespace App\Actions\Consultation;

use App\Models\Consultation;
use App\Models\Conversation;
use App\Models\Payment;

final class FinalizeConsultationPaymentAction
{
    private const ARCHITECT_PAYOUT_TAX_PERCENT = 10;

    public function execute(Payment $payment): Payment
    {
        if ($payment->status !== 'completed' || $payment->consultation_id !== null) {
            return $payment;
        }

        $consultationStart = now();
        $sessionFee = (int) $payment->amount;
        $payoutTaxAmount = (int) round($sessionFee * self::ARCHITECT_PAYOUT_TAX_PERCENT / 100);
        $payoutAmount = max(0, $sessionFee - $payoutTaxAmount);

        $consultation = Consultation::create([
            'user_id' => (string) $payment->user_id,
            'architect_id' => (string) $payment->architect_id,
            'payment_id' => (string) $payment->getKey(),
            'consultation_date' => $consultationStart,
            'duration_hours' => (int) $payment->duration_hours,
            'session_fee' => $sessionFee,
            'payout_tax_amount' => $payoutTaxAmount,
            'payout_amount' => $payoutAmount,
            'status' => 'active',
            'verification_status' => 'unverified',
            'payout_status' => 'pending',
        ]);

        $conversation = Conversation::create([
            'name' => 'Consultation Session',
            'is_group' => false,
            'participant_ids' => [
                (string) $payment->user_id,
                (string) $payment->architect_id,
            ],
            'last_read_at' => [
                (string) $payment->user_id => $consultationStart->toIso8601String(),
                (string) $payment->architect_id => $consultationStart->toIso8601String(),
            ],
            'consultation_id' => (string) $consultation->getKey(),
        ]);

        $consultation->conversation_id = (string) $conversation->getKey();
        $consultation->save();

        $payment->consultation_id = (string) $consultation->getKey();
        $payment->conversation_id = (string) $conversation->getKey();
        $payment->save();

        return $payment;
    }
}
