<?php

use App\Models\Consultation;
use App\Models\ConsultationReport;
use App\Models\Payment;
use App\Models\PaymentHistory;
use App\Models\User;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;

afterEach(function () {
    DB::connection('mongodb')->table('payment_histories')->delete();
    DB::connection('mongodb')->table('payments')->delete();
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
        ->assertJsonPath('data.pending_payouts_gross', 120000)
        ->assertJsonPath('data.pending_payouts_tax', 12000)
        ->assertJsonPath('data.pending_payouts', 108000);

    actingAs($admin, 'sanctum')
        ->postJson('/api/v1/consultations/payroll/queue/' . $architect->getKey() . '/release')
        ->assertOk()
        ->assertJsonPath('data.release_status', 'selesai')
        ->assertJsonPath('data.released_count', 2)
        ->assertJsonPath('data.released_gross_total_amount', 120000)
        ->assertJsonPath('data.released_total_tax', 12000)
        ->assertJsonPath('data.released_total_amount', 108000);
});

it('applies buyback to user when user report is approved', function () {
    config()->set('services.midtrans.server_key', 'midtrans-server-test');
    config()->set('services.midtrans.is_production', false);
    Http::preventStrayRequests();
    Http::fake(function (HttpRequest $request) {
        expect($request->url())->toContain('/v2/CONS-BUYBACK-USER-APPROVED/refund');

        return Http::response([
            'status_code' => '200',
            'transaction_id' => 'midtrans-refund-001',
            'order_id' => 'CONS-BUYBACK-USER-APPROVED',
            'refund_key' => 'refund-key-001',
        ], 200);
    });

    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $architect = User::factory()->architect()->create();

    $consultation = Consultation::create([
        'user_id' => (string) $user->getKey(),
        'architect_id' => (string) $architect->getKey(),
        'consultation_date' => now()->subHour(),
        'duration_hours' => 1,
        'session_fee' => 150000,
        'status' => 'completed',
        'verification_status' => 'verified',
        'payout_status' => 'pending',
    ]);

    $payment = Payment::create([
        'order_id' => 'CONS-BUYBACK-USER-APPROVED',
        'user_id' => (string) $user->getKey(),
        'architect_id' => (string) $architect->getKey(),
        'consultation_id' => (string) $consultation->getKey(),
        'amount' => 150000,
        'duration_hours' => 1,
        'status' => 'completed',
    ]);

    $report = ConsultationReport::create([
        'consultation_id' => (string) $consultation->getKey(),
        'requester_id' => (string) $user->getKey(),
        'opposing_party_id' => (string) $architect->getKey(),
        'requester_role' => 'user',
        'reason' => 'Konsultasi tidak sesuai.',
        'action_status' => 'new',
    ]);

    actingAs($admin, 'sanctum')
        ->putJson('/api/v1/consultations/reports/' . $report->getKey() . '/action', [
            'action' => 'approved',
        ])
        ->assertOk()
        ->assertJsonPath('data.buyback_applied', true)
        ->assertJsonPath('data.payment_status', 'completed')
        ->assertJsonPath('data.refund_status', 'approved');

    expect(Payment::find((string) $payment->getKey())?->status)->toBe('completed');
    expect(Payment::find((string) $payment->getKey())?->refund_status)->toBe('refunded');
    expect(Consultation::find((string) $consultation->getKey())?->payout_status)->toBe('refunded');
    expect(
        PaymentHistory::query()
            ->where('payment_id', (string) $payment->getKey())
            ->where('event', 'buyback_refund_applied')
            ->exists()
    )->toBeTrue();
});

it('applies buyback to user when architect report is declined', function () {
    config()->set('services.midtrans.server_key', 'midtrans-server-test');
    config()->set('services.midtrans.is_production', false);
    Http::preventStrayRequests();
    Http::fake(function (HttpRequest $request) {
        expect($request->url())->toContain('/v2/CONS-BUYBACK-ARCH-DECLINED/refund');

        return Http::response([
            'status_code' => '200',
            'transaction_id' => 'midtrans-refund-002',
            'order_id' => 'CONS-BUYBACK-ARCH-DECLINED',
            'refund_key' => 'refund-key-002',
        ], 200);
    });

    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $architect = User::factory()->architect()->create();

    $consultation = Consultation::create([
        'user_id' => (string) $user->getKey(),
        'architect_id' => (string) $architect->getKey(),
        'consultation_date' => now()->subHour(),
        'duration_hours' => 1,
        'session_fee' => 175000,
        'status' => 'completed',
        'verification_status' => 'verified',
        'payout_status' => 'pending',
    ]);

    $payment = Payment::create([
        'order_id' => 'CONS-BUYBACK-ARCH-DECLINED',
        'user_id' => (string) $user->getKey(),
        'architect_id' => (string) $architect->getKey(),
        'consultation_id' => (string) $consultation->getKey(),
        'amount' => 175000,
        'duration_hours' => 1,
        'status' => 'completed',
    ]);

    $report = ConsultationReport::create([
        'consultation_id' => (string) $consultation->getKey(),
        'requester_id' => (string) $architect->getKey(),
        'opposing_party_id' => (string) $user->getKey(),
        'requester_role' => 'architect',
        'reason' => 'User melanggar aturan.',
        'action_status' => 'new',
    ]);

    actingAs($admin, 'sanctum')
        ->putJson('/api/v1/consultations/reports/' . $report->getKey() . '/action', [
            'action' => 'declined',
        ])
        ->assertOk()
        ->assertJsonPath('data.buyback_applied', true)
        ->assertJsonPath('data.payment_status', 'completed')
        ->assertJsonPath('data.refund_status', 'approved');

    expect(Payment::find((string) $payment->getKey())?->status)->toBe('completed');
    expect(Payment::find((string) $payment->getKey())?->refund_status)->toBe('refunded');
    expect(Consultation::find((string) $consultation->getKey())?->payout_status)->toBe('refunded');
    expect(
        PaymentHistory::query()
            ->where('payment_id', (string) $payment->getKey())
            ->where('event', 'buyback_refund_applied')
            ->exists()
    )->toBeTrue();
});
