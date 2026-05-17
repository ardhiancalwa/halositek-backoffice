<?php

namespace App\Actions\Consultation;

use App\Models\Consultation;
use App\Models\ConsultationReport;
use App\Models\Message;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

final class BuildConsultationReportPayloadAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(ConsultationReport $report): array
    {
        $report->loadMissing(['consultation', 'requester', 'opposingParty', 'consultation.payment']);

        $consultation = $report->consultation;
        $requester = $report->requester;
        $opposing = $report->opposingParty;

        $consultationDate = $consultation?->consultation_date;
        $consultationDateIso = null;
        if ($consultationDate instanceof Carbon) {
            $consultationDateIso = $consultationDate->toIso8601String();
        } elseif (is_string($consultationDate) && $consultationDate !== '') {
            $consultationDateIso = Carbon::parse($consultationDate)->toIso8601String();
        }

        $nominal = 0;
        if ($consultation instanceof Consultation) {
            $payment = $consultation->payment;
            if (! ($payment instanceof Payment)) {
                $payment = Payment::query()
                    ->where('consultation_id', (string) $consultation->getKey())
                    ->first();
            }

            if ($payment instanceof Payment) {
                $nominal = (int) $payment->amount;
            } else {
                $nominal = (int) ($consultation->session_fee ?? 0);
            }
        }

        return [
            'id' => (string) $report->getKey(),
            'requester' => $this->buildRequesterPayload($requester, (string) $report->requester_role),
            'reason' => (string) $report->reason,
            'consultation_date' => $consultationDateIso,
            'opposing_party' => $this->buildOpposingPayload($opposing),
            'nominal' => $nominal,
            'transcript' => $this->buildTranscript($consultation, $requester, $opposing),
            'action_report' => (string) ($report->action_status ?? 'new'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildRequesterPayload(?User $requester, string $role): array
    {
        return [
            'id' => $requester ? (string) $requester->getKey() : '',
            'name' => $requester?->name ?? '',
            'role' => $role,
            'photo_profile' => $requester?->photo_profile,
            'photo_profile_url' => $requester && $requester->photo_profile
                ? Storage::url((string) $requester->photo_profile)
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildOpposingPayload(?User $opposing): array
    {
        return [
            'id' => $opposing ? (string) $opposing->getKey() : '',
            'name' => $opposing?->name ?? '',
            'photo_profile' => $opposing?->photo_profile,
            'photo_profile_url' => $opposing && $opposing->photo_profile
                ? Storage::url((string) $opposing->photo_profile)
                : null,
        ];
    }

    private function buildTranscript(?Consultation $consultation, ?User $requester, ?User $opposing): string
    {
        if (! ($consultation instanceof Consultation) || ! $consultation->conversation_id) {
            return is_string($consultation?->transcript) ? $consultation->transcript : '';
        }

        $participantIds = array_filter([
            $requester ? (string) $requester->getKey() : null,
            $opposing ? (string) $opposing->getKey() : null,
        ], fn ($value) => is_string($value) && $value !== '');

        $messages = Message::query()
            ->where('conversation_id', (string) $consultation->conversation_id)
            ->when($participantIds !== [], function ($query) use ($participantIds): void {
                $query->whereIn('user_id', array_map('strval', $participantIds));
            })
            ->orderBy('created_at')
            ->get();

        $lines = $messages->map(function (Message $message) use ($requester, $opposing): ?string {
            $body = $message->content;
            if (! is_string($body) || $body === '') {
                $body = is_string($message->body) ? $message->body : '';
            }
            $body = trim($body);
            if ($body === '') {
                return null;
            }

            $senderName = null;
            if ($requester && (string) $message->user_id === (string) $requester->getKey()) {
                $senderName = (string) $requester->name;
            } elseif ($opposing && (string) $message->user_id === (string) $opposing->getKey()) {
                $senderName = (string) $opposing->name;
            }

            if ($senderName === null || $senderName === '') {
                return $body;
            }

            return $senderName . ': ' . $body;
        })->filter()->implode("\n");

        if ($lines !== '') {
            return $lines;
        }

        return is_string($consultation->transcript) ? $consultation->transcript : '';
    }
}
