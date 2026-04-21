<?php

use App\Models\Award;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

afterEach(function () {
    DB::connection('mongodb')->table('awards')->delete();
    DB::connection('mongodb')->table('projects')->delete();
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
});

it('allows admin to filter users by status and search by name or email', function () {
    $admin = User::factory()->admin()->create();

    $matchedUser = User::factory()->create([
        'name' => 'Nadia Suspend',
        'email' => 'nadia.suspend@example.com',
        'account_status' => 'suspend',
    ]);

    User::factory()->create([
        'name' => 'Andi Active',
        'email' => 'andi.active@example.com',
        'account_status' => 'active',
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/users?status=suspend&search=nadia');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $matchedUser->id);
});

it('filters projects by status architect id and search keyword', function () {
    $architectA = User::factory()->architect()->create();
    $architectB = User::factory()->architect()->create();

    Project::create([
        'architect_id' => $architectA->id,
        'name' => 'Modern Alpha House',
        'style' => 'Tropical Modern',
        'estimated_cost' => 'Rp 2M - 3M',
        'status' => 'approved',
        'likes_count' => 0,
    ]);

    Project::create([
        'architect_id' => $architectA->id,
        'name' => 'Classic Residence',
        'style' => 'Classic',
        'estimated_cost' => 'Rp 1M - 2M',
        'status' => 'approved',
        'likes_count' => 0,
    ]);

    Project::create([
        'architect_id' => $architectB->id,
        'name' => 'Modern Beta House',
        'style' => 'Modern',
        'estimated_cost' => 'Rp 2M - 3M',
        'status' => 'approved',
        'likes_count' => 0,
    ]);

    $response = $this->getJson(
        "/api/v1/projects?status=approved&architect_id={$architectA->id}&search=modern"
    );

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.architect_id', $architectA->id)
        ->assertJsonPath('data.0.name', 'Modern Alpha House');
});

it('filters awards and includes pending and approved counts in meta', function () {
    $architectA = User::factory()->architect()->create();
    $architectB = User::factory()->architect()->create();

    Award::create([
        'architect_id' => $architectA->id,
        'name' => 'Alpha Award Pending',
        'project_name' => 'Alpha Project',
        'role' => 'Lead Architect',
        'award_date' => '2026-03-20',
        'status' => 'pending',
    ]);

    Award::create([
        'architect_id' => $architectA->id,
        'name' => 'Alpha Award Approved',
        'project_name' => 'Project Skyline',
        'role' => 'Lead Architect',
        'award_date' => '2026-03-20',
        'status' => 'approved',
    ]);

    Award::create([
        'architect_id' => $architectA->id,
        'name' => 'Unrelated Award',
        'project_name' => 'Other Project',
        'role' => 'Architect',
        'award_date' => '2026-03-20',
        'status' => 'approved',
    ]);

    Award::create([
        'architect_id' => $architectB->id,
        'name' => 'Alpha Award Other Architect',
        'project_name' => 'Alpha Project',
        'role' => 'Architect',
        'award_date' => '2026-03-20',
        'status' => 'pending',
    ]);

    $response = $this->getJson(
        "/api/v1/awards?architect_id={$architectA->id}&search=alpha"
    );

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('meta.pending_count', 1)
        ->assertJsonPath('meta.approved_count', 1);
});

it('enforces word count limits when creating award', function () {
    Storage::fake('public');

    $architect = User::factory()->architect()->create();

    $tooLongAwardName = 'one two three four five six seven eight nine ten eleven twelve thirteen';

    $response = $this->actingAs($architect, 'sanctum')->post('/api/v1/awards', [
        'name' => $tooLongAwardName,
        'project_name' => 'Valid project name',
        'role' => 'Lead Architect',
        'award_date' => '2026-03-20',
        'description' => 'Valid description text',
        'verification_file' => UploadedFile::fake()->create('proof.pdf', 100, 'application/pdf'),
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('errors.name.0', 'The name may not be greater than 12 words.');
});
