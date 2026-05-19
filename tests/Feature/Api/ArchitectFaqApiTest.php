<?php

use App\Models\ArchitectProfile;
use App\Models\Award;
use App\Models\Consultation;
use App\Models\Faq;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

afterEach(function () {
    DB::connection('mongodb')->table('awards')->delete();
    DB::connection('mongodb')->table('projects')->delete();
    DB::connection('mongodb')->table('architect_wishlists')->delete();
    DB::connection('mongodb')->table('architect_profiles')->delete();
    DB::connection('mongodb')->table('faqs')->delete();
    DB::connection('mongodb')->table('consultations')->delete();
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
});

it('returns architects on public architect index regardless of profile status', function () {
    $approvedArchitect = User::factory()->architect()->create();
    $pendingArchitect = User::factory()->architect()->create();

    ArchitectProfile::create([
        'user_id' => $approvedArchitect->id,
        'headline' => 'Approved architect',
        'status' => 'approved',
        'rating' => 4.7,
    ]);

    Project::create([
        'architect_id' => $approvedArchitect->id,
        'name' => 'Project One',
        'style' => 'modern',
        'estimated_cost' => 'Rp 2M - 3M',
        'status' => 'approved',
        'likes_count' => 0,
    ]);

    Award::create([
        'architect_id' => $approvedArchitect->id,
        'name' => 'Award One',
        'project_name' => 'Project One',
        'role' => 'Lead Architect',
        'award_date' => '2026-03-20',
        'status' => 'approved',
    ]);

    ArchitectProfile::create([
        'user_id' => $pendingArchitect->id,
        'headline' => 'Pending architect',
        'status' => 'pending',
        'rating' => 4.1,
    ]);

    $response = $this->getJson('/api/v1/architects');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(2, 'data');

    $ids = collect($response->json('data'))->pluck('id')->all();
    expect($ids)->toContain($approvedArchitect->id, $pendingArchitect->id);
});

it('can save and unsave architect wishlist for authenticated user', function () {
    $user = User::factory()->create();
    $architect = User::factory()->architect()->create();

    ArchitectProfile::create([
        'user_id' => $architect->id,
        'status' => 'approved',
        'rating' => 4.5,
    ]);

    $saveResponse = $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/architects/{$architect->id}/save");

    $saveResponse->assertOk()
        ->assertJsonPath('success', true);

    $wishlistResponse = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/architects/wishlist');

    $wishlistResponse->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $architect->id);

    $unsaveResponse = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/architects/{$architect->id}/save");

    $unsaveResponse->assertOk()
        ->assertJsonPath('success', true);
});

it('allows admin to verify architect status', function () {
    $admin = User::factory()->admin()->create();
    $architect = User::factory()->architect()->create();

    ArchitectProfile::create([
        'user_id' => $architect->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/architects/{$architect->id}/verify", [
            'status' => 'approved',
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', 'approved');
});

it('returns only active faqs for public index', function () {
    Faq::create([
        'question' => 'How to pay?',
        'answer' => 'Use supported payment channels.',
        'is_active' => true,
    ]);

    Faq::create([
        'question' => 'Hidden question',
        'answer' => 'Hidden answer',
        'is_active' => false,
    ]);

    $response = $this->getJson('/api/v1/faqs');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.question', 'How to pay?');
});

it('allows admin to manage faq lifecycle', function () {
    $admin = User::factory()->admin()->create();

    $store = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/faqs', [
            'question' => 'Can I update profile?',
            'answer' => 'Yes, from account settings.',
            'is_active' => true,
        ]);

    $store->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.question', 'Can I update profile?');

    $faqId = $store->json('data.id');

    $update = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/faqs/{$faqId}", [
            'answer' => 'Yes, from profile settings.',
        ]);

    $update->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.answer', 'Yes, from profile settings.');

    $delete = $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/v1/faqs/{$faqId}");

    $delete->assertOk()
        ->assertJsonPath('success', true);
});

it('allows an architect to retrieve their own released earnings list and totals', function () {
    $architect = User::factory()->architect()->create(['name' => 'Dimas Arsitek']);
    $user = User::factory()->create(['name' => 'Ayu Pratama']);
    $otherArchitect = User::factory()->architect()->create();

    // 1. Released Consultation for our architect
    Consultation::create([
        'user_id' => (string) $user->getKey(),
        'architect_id' => (string) $architect->getKey(),
        'consultation_date' => now()->subDays(2),
        'duration_hours' => 2,
        'session_fee' => 500000,
        'payout_status' => 'released',
        'payout_released_at' => now()->subDay(),
        'payout_tax_amount' => 50000,
        'payout_amount' => 450000,
        'status' => 'completed',
    ]);

    // 2. Pending Consultation for our architect (not yet released, should not show in earnings)
    Consultation::create([
        'user_id' => (string) $user->getKey(),
        'architect_id' => (string) $architect->getKey(),
        'consultation_date' => now(),
        'duration_hours' => 1,
        'session_fee' => 300000,
        'payout_status' => 'pending',
        'status' => 'completed',
    ]);

    // 3. Released Consultation for another architect
    Consultation::create([
        'user_id' => (string) $user->getKey(),
        'architect_id' => (string) $otherArchitect->getKey(),
        'consultation_date' => now()->subDays(3),
        'duration_hours' => 1,
        'session_fee' => 400000,
        'payout_status' => 'released',
        'payout_released_at' => now()->subDays(2),
        'payout_tax_amount' => 40000,
        'payout_amount' => 360000,
        'status' => 'completed',
    ]);

    // Test as authenticated architect
    $response = $this->actingAs($architect, 'sanctum')
        ->getJson('/api/v1/architects/earnings');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.total_gross_earnings', 500000)
        ->assertJsonPath('data.total_tax_paid', 50000)
        ->assertJsonPath('data.total_net_earnings', 450000)
        ->assertJsonCount(1, 'data.earnings')
        ->assertJsonPath('data.earnings.0.gross_fee', 500000)
        ->assertJsonPath('data.earnings.0.tax_deduction', 50000)
        ->assertJsonPath('data.earnings.0.net_earning', 450000)
        ->assertJsonPath('data.earnings.0.user.name', 'Ayu Pratama');

    // Test as standard user (forbidden)
    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/architects/earnings')
        ->assertForbidden();
});
