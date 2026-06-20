<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

afterEach(function () {
    DB::connection('mongodb')->table('saved_projects')->delete();
    DB::connection('mongodb')->table('projects')->delete();
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
});

it('allows authenticated user to save, unsave and list saved projects', function () {
    $user = User::factory()->create();
    $architect = User::factory()->architect()->create();

    $project = Project::create([
        'architect_id' => $architect->id,
        'name' => 'Dream Villa',
        'style' => 'modern',
        'estimated_cost' => 'Rp 5M - 10M',
        'status' => 'approved',
        'likes_count' => 0,
    ]);

    // 1. Initially, saved list should be empty
    actingAs($user, 'sanctum')
        ->getJson('/api/v1/projects/saved')
        ->assertOk()
        ->assertJsonCount(0, 'data');

    // 2. Save the project
    actingAs($user, 'sanctum')
        ->postJson("/api/v1/projects/{$project->id}/save")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Project berhasil disimpan.');

    // 3. Trying to save again should return 409 conflict
    actingAs($user, 'sanctum')
        ->postJson("/api/v1/projects/{$project->id}/save")
        ->assertStatus(409)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Project sudah tersimpan.');

    // 4. Saved list should now contain 1 project
    actingAs($user, 'sanctum')
        ->getJson('/api/v1/projects/saved')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $project->id)
        ->assertJsonPath('data.0.name', 'Dream Villa');

    // 5. Unsave the project
    actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/projects/{$project->id}/save")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Project berhasil dihapus dari daftar simpan.');

    // 6. Saved list should be empty again
    actingAs($user, 'sanctum')
        ->getJson('/api/v1/projects/saved')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('prevents saving non-existent projects', function () {
    $user = User::factory()->create();

    actingAs($user, 'sanctum')
        ->postJson('/api/v1/projects/non-existent-id/save')
        ->assertNotFound();
});

it('prevents unsaving non-saved projects', function () {
    $user = User::factory()->create();

    actingAs($user, 'sanctum')
        ->deleteJson('/api/v1/projects/some-id/save')
        ->assertNotFound();
});
