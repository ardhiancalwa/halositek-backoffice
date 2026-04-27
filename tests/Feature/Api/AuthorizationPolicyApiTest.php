<?php

use App\Models\Award;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

afterEach(function () {
    DB::connection('mongodb')->table('awards')->delete();
    DB::connection('mongodb')->table('projects')->delete();
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
});

it('allows admin to update project status only', function () {
    $admin = User::factory()->admin()->create();
    $architect = User::factory()->architect()->create();

    $project = Project::create([
        'architect_id' => $architect->id,
        'name' => 'Project Alpha',
        'style' => 'modern',
        'description' => 'Sample description',
        'images' => ['projects/images/a.jpg'],
        'layout_images' => ['projects/layouts/a.jpg'],
        'highlight_features' => 'Smart home integration',
        'estimated_cost' => 'Rp 3,8M - 4,6M',
        'area' => '120 m2',
        'status' => 'pending',
        'likes_count' => 0,
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/projects/{$project->id}", [
            'status' => 'approved',
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.project.status', 'approved');
});

it('rejects admin when updating project fields other than status', function () {
    $admin = User::factory()->admin()->create();
    $architect = User::factory()->architect()->create();

    $project = Project::create([
        'architect_id' => $architect->id,
        'name' => 'Project Beta',
        'style' => 'minimalist',
        'estimated_cost' => 'Rp 2M - 3M',
        'status' => 'pending',
        'likes_count' => 0,
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/projects/{$project->id}", [
            'status' => 'approved',
            'name' => 'Updated by Admin',
        ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false);
});

it('allows architect owner to update project details and resets status to pending', function () {
    $architect = User::factory()->architect()->create();

    $project = Project::create([
        'architect_id' => $architect->id,
        'name' => 'Project Gamma',
        'style' => 'industrial',
        'estimated_cost' => 'Rp 1,5M - 2M',
        'status' => 'approved',
        'likes_count' => 0,
    ]);

    $response = $this->actingAs($architect, 'sanctum')
        ->putJson("/api/v1/projects/{$project->id}", [
            'name' => 'Project Gamma Updated',
            'highlight_features' => 'Open-space loft',
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.project.name', 'Project Gamma Updated')
        ->assertJsonPath('data.project.status', 'pending');
});

it('forbids non-owner architect from updating project', function () {
    $owner = User::factory()->architect()->create();
    $otherArchitect = User::factory()->architect()->create();

    $project = Project::create([
        'architect_id' => $owner->id,
        'name' => 'Project Delta',
        'style' => 'modern',
        'estimated_cost' => 'Rp 2M - 2,5M',
        'status' => 'pending',
        'likes_count' => 0,
    ]);

    $response = $this->actingAs($otherArchitect, 'sanctum')
        ->putJson("/api/v1/projects/{$project->id}", [
            'name' => 'Unauthorized update',
        ]);

    $response->assertForbidden()
        ->assertJsonPath('success', false);
});

it('allows admin to update award status only', function () {
    $admin = User::factory()->admin()->create();
    $architect = User::factory()->architect()->create();

    $award = Award::create([
        'architect_id' => $architect->id,
        'name' => 'Design Gold',
        'project_name' => 'Project Alpha',
        'role' => 'Lead Architect',
        'award_date' => '2026-03-20',
        'description' => 'National competition winner',
        'verification_file' => 'awards/verification-files/proof.pdf',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/awards/{$award->id}", [
            'status' => 'approved',
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.award.status', 'approved');
});

it('rejects admin when updating award fields other than status', function () {
    $admin = User::factory()->admin()->create();
    $architect = User::factory()->architect()->create();

    $award = Award::create([
        'architect_id' => $architect->id,
        'name' => 'Urban Design Award',
        'project_name' => 'Project Beta',
        'role' => 'Architect',
        'award_date' => '2026-01-15',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/awards/{$award->id}", [
            'status' => 'declined',
            'name' => 'Not allowed field',
        ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false);
});

it('allows user to update own profile via me endpoint', function () {
    $user = User::factory()->create([
        'name' => 'Before Name',
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/me', [
            'name' => 'After Name',
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.name', 'After Name');
});

it('allows admin to update only user account status', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create([
        'account_status' => 'active',
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/users/{$user->id}", [
            'account_status' => 'suspend',
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.account_status', 'suspend');
});

it('rejects admin user update payload when account_status is missing', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/users/{$user->id}", [
            'name' => 'Should fail',
        ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false);
});
