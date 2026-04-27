<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Consultation;
use App\Models\ConsultationReport;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConsultationReportSeeder extends Seeder
{
    /**
     * Seed consultation reports for report management.
     */
    public function run(): void
    {
        $consultations = Consultation::query()->orderBy('consultation_date', 'desc')->take(6)->get();
        if ($consultations->isEmpty()) {
            return;
        }

        $admin = User::query()->where('role', UserRole::Admin->value)->first();

        foreach ($consultations as $index => $consultation) {
            $isUserRequester = $index % 2 === 0;
            $requesterId = $isUserRequester ? (string) $consultation->user_id : (string) $consultation->architect_id;
            $opposingId = $isUserRequester ? (string) $consultation->architect_id : (string) $consultation->user_id;
            $requesterRole = $isUserRequester ? 'user' : 'architect';

            $actionStatus = match ($index % 3) {
                0 => 'new',
                1 => 'approved',
                default => 'declined',
            };

            ConsultationReport::query()->create([
                'consultation_id' => (string) $consultation->getKey(),
                'requester_id' => $requesterId,
                'opposing_party_id' => $opposingId,
                'requester_role' => $requesterRole,
                'reason' => $isUserRequester
                    ? 'Arsitek tidak hadir pada jadwal konsultasi.'
                    : 'User tidak kooperatif selama konsultasi.',
                'action_status' => $actionStatus,
                'actioned_by' => $actionStatus === 'new' || ! $admin ? null : (string) $admin->getKey(),
                'actioned_at' => $actionStatus === 'new' ? null : now()->subDays(1),
            ]);
        }
    }
}
