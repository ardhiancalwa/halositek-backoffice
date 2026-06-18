<?php

use App\Models\ArchitectWishlist;
use App\Models\Award;
use App\Models\Consultation;
use App\Models\Project;
use App\Models\SavedProject;
use App\Models\User;
use Illuminate\Support\Facades\DB;

afterEach(function () {
    DB::connection('mongodb')->table('awards')->delete();
    DB::connection('mongodb')->table('architect_wishlists')->delete();
    DB::connection('mongodb')->table('consultations')->delete();
    DB::connection('mongodb')->table('projects')->delete();
    DB::connection('mongodb')->table('saved_projects')->delete();
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
});

it('keeps architect detail public without wishlist status', function () {
    $architect = User::factory()->architect()->create();

    $this->getJson("/api/v1/architects/{$architect->id}")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonMissingPath('data.is_wishlisted');
});

it('does not include projects and awards in architect list and detail responses', function () {
    $architect = User::factory()->architect()->create();

    Project::create([
        'architect_id' => (string) $architect->id,
        'name' => 'Modern Portfolio House',
        'style' => 'modern',
        'estimated_cost' => 'Rp 2M - 3M',
        'status' => 'approved',
        'likes_count' => 3,
    ]);

    Award::create([
        'architect_id' => (string) $architect->id,
        'name' => 'Best Residential Design',
        'project_name' => 'Modern Portfolio House',
        'role' => 'Lead Architect',
        'award_date' => '2026-03-20',
        'status' => 'approved',
    ]);

    $this->getJson('/api/v1/architects')
        ->assertOk()
        ->assertJsonMissingPath('data.0.projects')
        ->assertJsonMissingPath('data.0.awards');

    $this->getJson("/api/v1/architects/{$architect->id}")
        ->assertOk()
        ->assertJsonMissingPath('data.projects')
        ->assertJsonMissingPath('data.awards');
});

it('returns architect performance totals by architect id', function () {
    $architect = User::factory()->architect()->create();
    $otherArchitect = User::factory()->architect()->create();
    $user = User::factory()->create();

    $projectA = Project::create([
        'architect_id' => (string) $architect->id,
        'name' => 'Liked House A',
        'style' => 'modern',
        'estimated_cost' => 'Rp 2M - 3M',
        'status' => 'approved',
        'likes_count' => 5,
    ]);

    $projectB = Project::create([
        'architect_id' => (string) $architect->id,
        'name' => 'Liked House B',
        'style' => 'minimalist',
        'estimated_cost' => 'Rp 1M - 2M',
        'status' => 'approved',
        'likes_count' => 7,
    ]);

    $otherProject = Project::create([
        'architect_id' => (string) $otherArchitect->id,
        'name' => 'Other Architect House',
        'style' => 'industrial',
        'estimated_cost' => 'Rp 3M - 4M',
        'status' => 'approved',
        'likes_count' => 11,
    ]);

    SavedProject::create([
        'user_id' => (string) $user->id,
        'project_id' => (string) $projectA->id,
    ]);

    SavedProject::create([
        'user_id' => (string) $user->id,
        'project_id' => (string) $projectB->id,
    ]);

    SavedProject::create([
        'user_id' => (string) $user->id,
        'project_id' => (string) $otherProject->id,
    ]);

    Consultation::create([
        'user_id' => (string) $user->id,
        'architect_id' => (string) $architect->id,
        'consultation_date' => now(),
        'duration_hours' => 1,
        'session_fee' => 100000,
        'status' => 'completed',
        'verification_status' => 'verified',
        'payout_status' => 'released',
    ]);

    Consultation::create([
        'user_id' => (string) $user->id,
        'architect_id' => (string) $otherArchitect->id,
        'consultation_date' => now(),
        'duration_hours' => 1,
        'session_fee' => 100000,
        'status' => 'completed',
        'verification_status' => 'verified',
        'payout_status' => 'released',
    ]);

    $this->getJson("/api/v1/architects/{$architect->id}/performance")
        ->assertOk()
        ->assertJsonPath('data.architect_id', (string) $architect->id)
        ->assertJsonPath('data.total_likes', 12)
        ->assertJsonPath('data.total_saves', 2)
        ->assertJsonPath('data.total_consultations', 1);
});

it('includes wishlist status when a valid bearer token is provided', function () {
    $user = User::factory()->create();
    $architect = User::factory()->architect()->create();

    ArchitectWishlist::create([
        'user_id' => (string) $user->id,
        'architect_id' => (string) $architect->id,
    ]);

    $token = $user->createToken('access-token')->plainTextToken;

    $this->getJson("/api/v1/architects/{$architect->id}", [
        'Authorization' => 'Bearer ' . $token,
    ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.is_wishlisted', true);
});

it('returns false wishlist status when the authenticated user has not saved the architect', function () {
    $user = User::factory()->create();
    $architect = User::factory()->architect()->create();

    $token = $user->createToken('access-token')->plainTextToken;

    $this->getJson("/api/v1/architects/{$architect->id}", [
        'Authorization' => 'Bearer ' . $token,
    ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.is_wishlisted', false);
});
