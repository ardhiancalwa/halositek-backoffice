<?php

use App\Models\ArchitectProfile;
use App\Models\ArchitectWishlist;
use App\Models\Award;
use App\Models\Payment;
use App\Models\Project;
use App\Models\SavedProject;
use App\Models\User;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

afterEach(function () {
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('architect_profiles')->delete();
    DB::connection('mongodb')->table('projects')->delete();
    DB::connection('mongodb')->table('awards')->delete();
    DB::connection('mongodb')->table('saved_projects')->delete();
    DB::connection('mongodb')->table('architect_wishlists')->delete();
    DB::connection('mongodb')->table('payments')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
});

it('includes projects and awards in public architect list and show', function () {
    $architect = User::factory()->architect()->create([
        'name' => 'John Doe Architect',
    ]);

    ArchitectProfile::create([
        'user_id' => $architect->id,
        'headline' => 'Approved headline',
        'status' => 'approved',
    ]);

    $project = Project::create([
        'architect_id' => $architect->id,
        'name' => 'Glass Mansion',
        'style' => 'modern',
        'estimated_cost' => 'Rp 5M',
        'status' => 'approved',
        'likes_count' => 0,
    ]);

    $award = Award::create([
        'architect_id' => $architect->id,
        'name' => 'Best Eco Design 2026',
        'project_name' => 'Eco House',
        'role' => 'Principal',
        'award_date' => '2026-05-17',
        'status' => 'approved',
    ]);

    // 1. Check GET /architects
    $responseList = $this->getJson('/api/v1/architects');
    $responseList->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.projects.0.name', 'Glass Mansion')
        ->assertJsonPath('data.0.awards.0.name', 'Best Eco Design 2026');

    // 2. Check GET /architects/{id}
    $responseShow = $this->getJson("/api/v1/architects/{$architect->id}");
    $responseShow->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.projects.0.name', 'Glass Mansion')
        ->assertJsonPath('data.awards.0.name', 'Best Eco Design 2026');
});

it('includes saved projects, saved architects, and payment history in GET /me', function () {
    $user = User::factory()->create();
    $architect = User::factory()->architect()->create([
        'name' => 'Jane the Architect',
    ]);

    ArchitectProfile::create([
        'user_id' => $architect->id,
        'headline' => 'Approved Jane',
        'status' => 'approved',
    ]);

    $project = Project::create([
        'architect_id' => $architect->id,
        'name' => 'Cabin in the Woods',
        'style' => 'minimalist',
        'estimated_cost' => 'Rp 1M',
        'status' => 'approved',
        'likes_count' => 0,
    ]);

    // Save project
    SavedProject::create([
        'user_id' => (string) $user->id,
        'project_id' => $project->id,
    ]);

    // Save architect
    ArchitectWishlist::create([
        'user_id' => (string) $user->id,
        'architect_id' => $architect->id,
    ]);

    // Create payment
    Payment::create([
        'user_id' => (string) $user->id,
        'architect_id' => $architect->id,
        'order_id' => 'CONS-20260517-TESTPAY',
        'amount' => 150000,
        'duration_hours' => 2,
        'status' => 'completed',
        'payment_method' => 'bank_transfer',
        'paid_at' => now(),
    ]);

    // Hit GET /me
    $response = actingAs($user, 'sanctum')
        ->getJson('/api/v1/me');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', $user->name)
        // Check saved projects
        ->assertJsonCount(1, 'data.saved_projects')
        ->assertJsonPath('data.saved_projects.0.name', 'Cabin in the Woods')
        // Check saved architects
        ->assertJsonCount(1, 'data.saved_architects')
        ->assertJsonPath('data.saved_architects.0.name', 'Jane the Architect')
        // Check payment history
        ->assertJsonCount(1, 'data.payment_histories')
        ->assertJsonPath('data.payment_histories.0.order_id', 'CONS-20260517-TESTPAY')
        ->assertJsonPath('data.payment_histories.0.amount', 150000);
});

it('can register, update, and retrieve architect headline', function () {
    // 1. Register with a headline
    $registerResponse = $this->postJson('/api/v1/auth/register', [
        'name' => 'Registered Architect',
        'email' => 'reg_arch@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'architect',
        'headline' => 'Expert Eco Architect',
    ]);

    $registerResponse->assertCreated()
        ->assertJsonPath('success', true);

    $user = User::where('email', 'reg_arch@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->architectProfile->headline)->toBe('Expert Eco Architect');

    // 2. Retrieve via GET /me and check the headline is in UserResource
    $meResponse = actingAs($user, 'sanctum')->getJson('/api/v1/me');
    $meResponse->assertOk()
        ->assertJsonPath('data.headline', 'Expert Eco Architect');

    // 3. Update the headline via profile update
    $updateResponse = actingAs($user, 'sanctum')->postJson('/api/v1/me', [
        'name' => 'Registered Architect Updated',
        'headline' => 'Renowned Modern Architect',
    ]);

    $updateResponse->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.name', 'Registered Architect Updated')
        ->assertJsonPath('data.user.headline', 'Renowned Modern Architect');

    // Check model directly
    $user->refresh();
    expect($user->architectProfile->headline)->toBe('Renowned Modern Architect');
});
