<?php

use App\Enums\UserRole;
use App\Models\ArchitectProfile;
use App\Models\Consultation;
use App\Models\Conversation;
use App\Models\Payment;
use App\Models\PaymentHistory;
use App\Models\User;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;

afterEach(function () {
    DB::connection('mongodb')->table('messages')->delete();
    DB::connection('mongodb')->table('consultations')->delete();
    DB::connection('mongodb')->table('conversations')->delete();
    DB::connection('mongodb')->table('payment_histories')->delete();
    DB::connection('mongodb')->table('payments')->delete();
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
});

it('initiates midtrans payment for consultation', function () {
    config()->set('services.midtrans.server_key', 'midtrans-server-test');
    config()->set('services.midtrans.is_production', false);

    Http::preventStrayRequests();
    Http::fake(function (HttpRequest $request) {
        expect($request->url())->toEndWith('/snap/v1/transactions');

        return Http::response([
            'token' => 'snap-token-123',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123',
        ], 201);
    });

    $user = User::factory()->create(['role' => UserRole::User->value]);
    $architect = User::factory()->architect()->create();
    ArchitectProfile::create([
        'user_id' => (string) $architect->getKey(),
        'status' => 'approved',
        'consultation_fee' => 150000,
        'consultation_duration' => 2,
    ]);

    actingAs($user, 'sanctum')
        ->postJson('/api/v1/consultations/payments/initiate', [
            'architect_id' => (string) $architect->getKey(),
        ])
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.snap_token', 'snap-token-123')
        ->assertJsonPath('data.consultation_details.consultation_amount', 150000)
        ->assertJsonPath('data.consultation_details.tax_amount', 15000)
        ->assertJsonPath('data.consultation_details.total_amount', 165000)
        ->assertJsonPath('data.consultation_details.architect_name', (string) $architect->name);

    expect(PaymentHistory::query()->where('event', 'payment_initiated')->count())->toBe(1);
    expect(PaymentHistory::query()->where('event', 'snap_token_created')->count())->toBe(1);
});

it('blocks new payment for same architect while session is still active but allows other architect', function () {
    config()->set('services.midtrans.server_key', 'midtrans-server-test');
    config()->set('services.midtrans.is_production', false);

    Http::preventStrayRequests();
    Http::fake(fn () => Http::response([
        'token' => 'snap-token-active-check',
        'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-active-check',
    ], 201));

    $user = User::factory()->create(['role' => UserRole::User->value]);
    $architectA = User::factory()->architect()->create();
    $architectB = User::factory()->architect()->create();

    ArchitectProfile::create([
        'user_id' => (string) $architectA->getKey(),
        'status' => 'approved',
        'consultation_fee' => 100000,
        'consultation_duration' => 1,
    ]);
    ArchitectProfile::create([
        'user_id' => (string) $architectB->getKey(),
        'status' => 'approved',
        'consultation_fee' => 100000,
        'consultation_duration' => 1,
    ]);

    Consultation::create([
        'user_id' => (string) $user->getKey(),
        'architect_id' => (string) $architectA->getKey(),
        'consultation_date' => now()->subMinutes(20),
        'duration_hours' => 2,
        'session_fee' => 150000,
        'status' => 'active',
        'verification_status' => 'unverified',
        'payout_status' => 'pending',
    ]);

    actingAs($user, 'sanctum')
        ->postJson('/api/v1/consultations/payments/initiate', [
            'architect_id' => (string) $architectA->getKey(),
        ])
        ->assertStatus(422)
        ->assertJsonPath('errors.architect_id.0', 'Sesi konsultasi dengan arsitek ini masih berjalan. Tunggu sesi selesai untuk membuat sesi baru.');

    actingAs($user, 'sanctum')
        ->postJson('/api/v1/consultations/payments/initiate', [
            'architect_id' => (string) $architectB->getKey(),
        ])
        ->assertCreated()
        ->assertJsonPath('data.consultation_details.architect_id', (string) $architectB->getKey());
});

it('handles midtrans webhook and creates consultation session with conversation', function () {
    config()->set('services.midtrans.server_key', 'midtrans-server-test');
    config()->set('services.midtrans.is_production', false);

    Http::preventStrayRequests();
    Http::fake(fn () => Http::response([
        'token' => 'snap-token-abc',
        'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-abc',
    ], 201));

    $user = User::factory()->create(['role' => UserRole::User->value]);
    $architect = User::factory()->architect()->create();
    ArchitectProfile::create([
        'user_id' => (string) $architect->getKey(),
        'status' => 'approved',
        'consultation_fee' => 100000,
        'consultation_duration' => 1,
    ]);

    $initiateResponse = actingAs($user, 'sanctum')
        ->postJson('/api/v1/consultations/payments/initiate', [
            'architect_id' => (string) $architect->getKey(),
        ])
        ->assertCreated();

    $paymentId = (string) $initiateResponse->json('data.payment_id');
    $orderId = (string) $initiateResponse->json('data.order_id');
    $grossAmount = '110000.00';
    $statusCode = '200';
    $signature = hash('sha512', $orderId . $statusCode . $grossAmount . 'midtrans-server-test');

    $webhookResponse = $this->postJson('/api/v1/webhooks/midtrans', [
        'order_id' => $orderId,
        'status_code' => $statusCode,
        'transaction_status' => 'settlement',
        'transaction_id' => 'trx-001',
        'gross_amount' => $grossAmount,
        'payment_type' => 'bank_transfer',
        'signature_key' => $signature,
    ])->assertOk();

    $consultationId = (string) $webhookResponse->json('data.consultation_id');
    $conversationId = (string) $webhookResponse->json('data.conversation_id');
    $transactionId = 'trx-001';

    expect(Payment::find($paymentId)?->status)->toBe('completed');
    expect(Consultation::find($consultationId))->not->toBeNull();
    expect(Conversation::find($conversationId))->not->toBeNull();

    actingAs($user, 'sanctum')
        ->getJson("/api/v1/consultations/payments/{$transactionId}/status")
        ->assertOk()
        ->assertJsonPath('data.status', 'completed')
        ->assertJsonPath('data.can_enter_consultation', true)
        ->assertJsonPath('data.conversation_id', $conversationId);

    actingAs($user, 'sanctum')
        ->getJson('/api/v1/consultations/payments/history')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.status', 'completed');

    expect(PaymentHistory::query()->where('payment_id', $paymentId)->count())->toBeGreaterThanOrEqual(3);
});

it('blocks sending new messages when consultation session is expired', function () {
    Http::fake();

    $user = User::factory()->create(['role' => UserRole::User->value]);
    $architect = User::factory()->architect()->create();

    $consultation = Consultation::create([
        'user_id' => (string) $user->getKey(),
        'architect_id' => (string) $architect->getKey(),
        'consultation_date' => now()->subHours(3),
        'duration_hours' => 1,
        'session_fee' => 200000,
        'status' => 'active',
        'verification_status' => 'unverified',
        'payout_status' => 'pending',
    ]);

    $conversation = Conversation::create([
        'name' => 'Expired Consultation Chat',
        'is_group' => false,
        'participant_ids' => [(string) $user->getKey(), (string) $architect->getKey()],
        'last_read_at' => [
            (string) $user->getKey() => now()->toIso8601String(),
            (string) $architect->getKey() => now()->toIso8601String(),
        ],
        'consultation_id' => (string) $consultation->getKey(),
    ]);

    actingAs($user, 'sanctum')
        ->postJson('/api/v1/chat/messages', [
            'conversation_id' => (string) $conversation->getKey(),
            'body' => 'Halo, masih bisa kirim?',
        ])
        ->assertStatus(422)
        ->assertJsonPath('message', 'Validasi gagal.')
        ->assertJsonPath('errors.conversation_id.0', 'Sesi konsultasi sudah berakhir, chat hanya dapat dibaca.');

    expect(Consultation::find((string) $consultation->getKey())?->status)->toBe('completed');
});

it('resolves active conversation when user has multiple sessions with the same architect', function () {
    Http::fake();

    $user = User::factory()->create(['role' => UserRole::User->value]);
    $architect = User::factory()->architect()->create();

    // 1. Expired session and conversation
    $oldConsultation = Consultation::create([
        'user_id' => (string) $user->getKey(),
        'architect_id' => (string) $architect->getKey(),
        'consultation_date' => now()->subHours(5),
        'duration_hours' => 1,
        'session_fee' => 150000,
        'status' => 'completed',
        'verification_status' => 'verified',
        'payout_status' => 'pending',
    ]);

    $oldConversation = Conversation::create([
        'name' => 'Expired Session Chat',
        'is_group' => false,
        'participant_ids' => [(string) $user->getKey(), (string) $architect->getKey()],
        'last_read_at' => [
            (string) $user->getKey() => now()->toIso8601String(),
            (string) $architect->getKey() => now()->toIso8601String(),
        ],
        'consultation_id' => (string) $oldConsultation->getKey(),
    ]);
    $oldConsultation->conversation_id = (string) $oldConversation->getKey();
    $oldConsultation->save();

    // 2. New active session and conversation
    $newConsultation = Consultation::create([
        'user_id' => (string) $user->getKey(),
        'architect_id' => (string) $architect->getKey(),
        'consultation_date' => now(),
        'duration_hours' => 1,
        'session_fee' => 150000,
        'status' => 'active',
        'verification_status' => 'unverified',
        'payout_status' => 'pending',
    ]);

    $newConversation = Conversation::create([
        'name' => 'Active Session Chat',
        'is_group' => false,
        'participant_ids' => [(string) $user->getKey(), (string) $architect->getKey()],
        'last_read_at' => [
            (string) $user->getKey() => now()->toIso8601String(),
            (string) $architect->getKey() => now()->toIso8601String(),
        ],
        'consultation_id' => (string) $newConsultation->getKey(),
    ]);
    $newConsultation->conversation_id = (string) $newConversation->getKey();
    $newConsultation->save();

    // Request to start conversation with this architect
    actingAs($user, 'sanctum')
        ->postJson('/api/v1/chat/conversations', [
            'is_group' => false,
            'participant_ids' => [(string) $architect->getKey()],
        ])
        ->assertStatus(201)
        // Assert that the returned conversation is the NEW active one
        ->assertJsonPath('data.id', (string) $newConversation->getKey())
        ->assertJsonPath('data.consultation_session.is_active', true)
        ->assertJsonPath('data.can_send_message', true);
});
