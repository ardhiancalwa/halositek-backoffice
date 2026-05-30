<?php

use App\Models\ArchitectProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

afterEach(function () {
    DB::connection('mongodb')->table('architect_profiles')->delete();
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
});

it('allows architect to update architect profile fields', function () {
    $architect = User::factory()->architect()->create([
        'name' => 'Initial Architect',
        'email' => 'architect-initial@example.com',
    ]);

    ArchitectProfile::create([
        'user_id' => (string) $architect->id,
        'headline' => 'Old headline',
        'bio' => 'Old bio',
        'consultation_fee' => 100000,
        'consultation_duration' => 1,
        'year_of_experience' => 2,
    ]);

    $response = $this->actingAs($architect, 'sanctum')
        ->postJson('/api/v1/architects/profile', [
            'name' => 'Updated Architect',
            'email' => 'architect-updated@example.com',
            'headline' => 'Green Architecture Expert',
            'bio' => 'Focused on sustainable residential projects.',
            'year_of_experience' => 8,
            'consultation_fee' => 250000,
            'consultation_hours' => 2,
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'Updated Architect')
        ->assertJsonPath('data.email', 'architect-updated@example.com')
        ->assertJsonPath('data.headline', 'Green Architecture Expert')
        ->assertJsonPath('data.bio', 'Focused on sustainable residential projects.')
        ->assertJsonPath('data.year_of_experience', 8)
        ->assertJsonPath('data.consultation_fee', 250000)
        ->assertJsonPath('data.consultation_hours', 2);

    $architect->refresh();
    expect($architect->name)->toBe('Updated Architect')
        ->and($architect->email)->toBe('architect-updated@example.com');

    $profile = $architect->architectProfile()->first();
    expect($profile)->not->toBeNull()
        ->and($profile?->headline)->toBe('Green Architecture Expert')
        ->and($profile?->bio)->toBe('Focused on sustainable residential projects.')
        ->and($profile?->year_of_experience)->toBe(8)
        ->and($profile?->consultation_fee)->toBe(250000)
        ->and($profile?->consultation_duration)->toBe(2);
});

it('forbids non architect user from updating architect profile endpoint', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/architects/profile', [
            'headline' => 'Should fail',
        ]);

    $response->assertForbidden()
        ->assertJsonPath('success', false);
});

it('updates architect photo profile using multipart form data', function () {
    Storage::fake('public');

    $architect = User::factory()->architect()->create([
        'photo_profile' => 'users/profiles/old-photo.jpg',
    ]);

    Storage::disk('public')->put('users/profiles/old-photo.jpg', 'old-content');

    $photo = UploadedFile::fake()->image('new-photo.jpg');

    $response = $this->actingAs($architect, 'sanctum')
        ->post('/api/v1/architects/profile', [
            'photo_profile' => $photo,
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true);

    $architect->refresh();
    expect($architect->photo_profile)->not->toBeNull();
    Storage::disk('public')->assertExists($architect->photo_profile);
    Storage::disk('public')->assertMissing('users/profiles/old-photo.jpg');
});

it('can still login after architect profile update', function () {
    $password = 'password123';
    $architect = User::factory()->architect()->create([
        'email' => 'before-login@example.com',
        'password' => bcrypt($password),
    ]);

    $this->actingAs($architect, 'sanctum')
        ->post('/api/v1/architects/profile', [
            'email' => '  AFTER-LOGIN@EXAMPLE.COM ',
            'headline' => 'Updated',
        ])->assertOk();

    $login = $this->postJson('/api/v1/auth/login', [
        'email' => 'after-login@example.com',
        'password' => $password,
        'role' => 'architect',
    ]);

    $login->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.email', 'after-login@example.com');
});

it('returns validation error when no update payload is sent', function () {
    $architect = User::factory()->architect()->create();

    $response = $this->actingAs($architect, 'sanctum')
        ->postJson('/api/v1/architects/profile', []);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('errors.request.0', 'Tidak ada data yang dikirim untuk diperbarui.');
});
