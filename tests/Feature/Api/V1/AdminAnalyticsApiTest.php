<?php

use App\Models\ArchitectProfile;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

afterEach(function () {
    Carbon::setTestNow();

    DB::connection('mongodb')->table('projects')->delete();
    DB::connection('mongodb')->table('architect_profiles')->delete();
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
});

it('returns analytics overview for admin with expected counts', function () {
    $admin = User::factory()->admin()->create();

    User::factory()->create();
    User::factory()->create();

    $approvedArchitect = User::factory()->architect()->create();
    User::factory()->architect()->create();

    ArchitectProfile::create([
        'user_id' => $approvedArchitect->id,
        'status' => 'approved',
    ]);

    Project::create([
        'architect_id' => $approvedArchitect->id,
        'name' => 'Project Active',
        'style' => 'modern',
        'estimated_cost' => 'Rp 2M - 3M',
        'status' => 'approved',
        'likes_count' => 0,
    ]);

    Project::create([
        'architect_id' => $approvedArchitect->id,
        'name' => 'Project Pending',
        'style' => 'modern',
        'estimated_cost' => 'Rp 2M - 3M',
        'status' => 'pending',
        'likes_count' => 0,
    ]);

    $response = actingAs($admin, 'sanctum')
        ->getJson('/api/v1/analytics/overview');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.registered_user', 2)
        ->assertJsonPath('data.registered_architect', 1)
        ->assertJsonPath('data.active_projects', 1);
});

it('forbids non admin user from accessing analytics endpoint', function () {
    $user = User::factory()->create();

    $response = actingAs($user, 'sanctum')
        ->getJson('/api/v1/analytics/overview');

    $response->assertForbidden();
});

it('returns user growth for last 7 days with zero-filled buckets', function () {
    Carbon::setTestNow('2026-04-24 10:00:00');

    $admin = User::factory()->admin()->create();

    $userToday = User::factory()->create();
    $userToday->created_at = now()->copy();
    $userToday->save();

    $userTwoDaysAgo = User::factory()->create();
    $userTwoDaysAgo->created_at = now()->copy()->subDays(2);
    $userTwoDaysAgo->save();

    $oldUser = User::factory()->create();
    $oldUser->created_at = now()->copy()->subDays(10);
    $oldUser->save();

    $response = actingAs($admin, 'sanctum')
        ->getJson('/api/v1/analytics/user-growth?range=last_7_days');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(7, 'data.labels')
        ->assertJsonCount(7, 'data.series')
        ->assertJsonCount(7, 'data.cumulative_series')
        ->assertJsonPath('data.total', 2)
        ->assertJsonPath('data.labels.0', '2026-04-20')
        ->assertJsonPath('data.labels.6', '2026-04-26')
        ->assertJsonPath('data.series.2', 1)
        ->assertJsonPath('data.cumulative_series.2', 1)
        ->assertJsonPath('data.cumulative_series.4', 2)
        ->assertJsonPath('data.series.4', 1);
});

it('returns architect growth for last month with weekly aggregation', function () {
    Carbon::setTestNow('2026-04-24 10:00:00');

    $admin = User::factory()->admin()->create();

    $approvedArchitect = User::factory()->architect()->create();
    ArchitectProfile::create([
        'user_id' => $approvedArchitect->id,
        'status' => 'approved',
    ]);
    $approvedArchitect->created_at = now()->copy()->subWeeks(1);
    $approvedArchitect->save();

    $growthResponse = actingAs($admin, 'sanctum')
        ->getJson('/api/v1/analytics/architect-growth?range=last_month');

    $growthResponse->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(count($growthResponse->json('data.labels')), 'data.series')
        ->assertJsonCount(count($growthResponse->json('data.labels')), 'data.cumulative_series')
        ->assertJsonPath('data.total', 1);
});
