<?php

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Support\Facades\DB;

afterEach(function () {
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
});

beforeEach(function () {
    $this->user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);
});

it('can login with valid credentials', function () {
    $response = $this->postJson('/api/v1/login', [
        'email' => $this->user->email,
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'message',
            'token',
            'user',
        ]);
});

it('cannot login with invalid credentials', function () {
    $response = $this->postJson('/api/v1/login', [
        'email' => $this->user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertUnauthorized()
        ->assertJsonStructure(['message']);
});

it('can get authenticated user profile', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/me');

    $response->assertOk()
        ->assertJsonPath('user.email', $this->user->email);
});

it('can logout', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/logout');

    $response->assertOk()
        ->assertJsonPath('message', 'Logged out successfully.');
});

it('returns 401 for unauthenticated requests', function () {
    $response = $this->getJson('/api/v1/me');

    $response->assertUnauthorized();
});
