<?php

use App\Models\Consultation;
use App\Models\ConsultationReport;
use App\Models\User;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

afterEach(function () {
    DB::connection('mongodb')->table('consultation_reports')->delete();
    DB::connection('mongodb')->table('consultations')->delete();
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
});

it('returns report stats and filtered report list for admin', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $architect = User::factory()->architect()->create();

    $consultation = Consultation::create([
        'user_id' => (string) $user->getKey(),
        'architect_id' => (string) $architect->getKey(),
        'consultation_date' => now()->subDay(),
        'duration_hours' => 1,
        'session_fee' => 25000,
        'transcript' => 'History transcript.',
        'status' => 'completed',
        'verification_status' => 'verified',
        'payout_status' => 'pending',
    ]);

    ConsultationReport::create([
        'consultation_id' => (string) $consultation->getKey(),
        'requester_id' => (string) $user->getKey(),
        'opposing_party_id' => (string) $architect->getKey(),
        'requester_role' => 'user',
        'reason' => 'Konsultasi tidak sesuai ekspektasi.',
        'action_status' => 'new',
    ]);

    ConsultationReport::create([
        'consultation_id' => (string) $consultation->getKey(),
        'requester_id' => (string) $architect->getKey(),
        'opposing_party_id' => (string) $user->getKey(),
        'requester_role' => 'architect',
        'reason' => 'Pelanggaran etika saat konsultasi.',
        'action_status' => 'approved',
    ]);

    actingAs($admin, 'sanctum')
        ->getJson('/api/v1/consultations/reports/stats')
        ->assertOk()
        ->assertJsonPath('data.total_report', 2)
        ->assertJsonPath('data.new_report', 1)
        ->assertJsonPath('data.user_report', 1)
        ->assertJsonPath('data.architect_report', 1);

    actingAs($admin, 'sanctum')
        ->getJson('/api/v1/consultations/reports?role=user&per_page=10')
        ->assertOk()
        ->assertJsonPath('data.0.requester.role', 'user')
        ->assertJsonPath('meta.total', 1);
});

it('updates report action and releases pending payroll', function () {
    $admin = User::factory()->admin()->create();
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $architect = User::factory()->architect()->create();

    $consultationA = Consultation::create([
        'user_id' => (string) $userA->getKey(),
        'architect_id' => (string) $architect->getKey(),
        'consultation_date' => now()->subHours(3),
        'duration_hours' => 1,
        'session_fee' => 50000,
        'status' => 'completed',
        'verification_status' => 'verified',
        'payout_status' => 'pending',
    ]);

    Consultation::create([
        'user_id' => (string) $userB->getKey(),
        'architect_id' => (string) $architect->getKey(),
        'consultation_date' => now()->subHours(2),
        'duration_hours' => 1,
        'session_fee' => 70000,
        'status' => 'completed',
        'verification_status' => 'verified',
        'payout_status' => 'pending',
    ]);

    $report = ConsultationReport::create([
        'consultation_id' => (string) $consultationA->getKey(),
        'requester_id' => (string) $userA->getKey(),
        'opposing_party_id' => (string) $architect->getKey(),
        'requester_role' => 'user',
        'reason' => 'Report test.',
        'action_status' => 'new',
    ]);

    actingAs($admin, 'sanctum')
        ->putJson('/api/v1/consultations/reports/' . $report->getKey() . '/action', [
            'action' => 'declined',
        ])
        ->assertOk()
        ->assertJsonPath('data.action_report', 'declined');

    actingAs($admin, 'sanctum')
        ->getJson('/api/v1/consultations/payroll/summary')
        ->assertOk()
        ->assertJsonPath('data.pending_payouts', 120000);

    actingAs($admin, 'sanctum')
        ->postJson('/api/v1/consultations/payroll/queue/' . $architect->getKey() . '/release')
        ->assertOk()
        ->assertJsonPath('data.release_status', 'selesai')
        ->assertJsonPath('data.released_count', 2)
        ->assertJsonPath('data.released_total_amount', 120000);
});
