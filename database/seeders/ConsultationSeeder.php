<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Consultation;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConsultationSeeder extends Seeder
{
    /**
     * Seed consultations for payroll management.
     */
    public function run(): void
    {
        $architects = User::query()->where('role', UserRole::Architect->value)->take(2)->get();
        $users = User::query()->where('role', UserRole::User->value)->take(5)->get();

        if ($architects->isEmpty() || $users->isEmpty()) {
            return;
        }

        foreach ($architects as $architectIndex => $architect) {
            foreach (range(1, 6) as $i) {
                $user = $users->get(($architectIndex + $i) % $users->count());
                $fee = 250000 + (50000 * ($i % 3));
                $isVerified = $i % 2 === 0;
                $isReleased = $i % 4 === 0;

                $createdAt = now()->subDays($i + ($architectIndex * 2));

                Consultation::query()->create([
                    'user_id' => (string) $user->getKey(),
                    'architect_id' => (string) $architect->getKey(),
                    'consultation_date' => $createdAt->copy()->addHours(2),
                    'duration_hours' => 1,
                    'session_fee' => $fee,
                    'transcript' => 'User menjelaskan kebutuhan. Architect memberikan arahan desain dan estimasi.',
                    'status' => 'completed',
                    'verification_status' => $isVerified ? 'verified' : 'unverified',
                    'payout_status' => $isReleased ? 'released' : 'pending',
                    'payout_released_at' => $isReleased ? $createdAt->copy()->addDay() : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }
}
