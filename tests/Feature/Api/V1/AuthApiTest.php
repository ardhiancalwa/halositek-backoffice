<?php

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

afterEach(function () {
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
});

/*
|--------------------------------------------------------------------------
| Register Tests
|--------------------------------------------------------------------------
*/

it('can register as user', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => 'Test User',
        'email' => 'user@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'success',
            'status_code',
            'message',
            'data' => [
                'name',
                'email',
                'role',
                'id',
                'access_token',
                'refresh_token',
                'token_type',
                'expires_in',
            ],
        ])
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.role', 'user')
        ->assertJsonPath('data.token_type', 'Bearer');

    expect(User::where('email', 'user@example.com')->exists())->toBeTrue();
});

it('can register as architect', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => 'Architect User',
        'email' => 'architect@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'architect',
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.role', 'architect');
});

it('cannot register as admin', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'admin',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('errors.role.0', 'You can only register as a user or architect.');
});

it('cannot register with duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->postJson('/api/v1/register', [
        'name' => 'Duplicate User',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false);
});

it('cannot register without password confirmation', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => 'Test User',
        'email' => 'user@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false);
});

/*
|--------------------------------------------------------------------------
| Login Tests
|--------------------------------------------------------------------------
*/

it('can login with valid credentials', function () {
    User::factory()->create([
        'email' => 'login@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'login@example.com',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'status_code',
            'message',
            'data' => [
                'name',
                'email',
                'role',
                'id',
                'access_token',
                'refresh_token',
                'token_type',
                'expires_in',
            ],
        ])
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Login successful.');
});

it('cannot login with invalid credentials', function () {
    User::factory()->create([
        'email' => 'login@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'login@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'The provided credentials are incorrect.');
});

/*
|--------------------------------------------------------------------------
| Refresh Token Tests
|--------------------------------------------------------------------------
*/

it('can refresh token', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    // Login to get tokens
    $loginResponse = $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $refreshToken = $loginResponse->json('data.refresh_token');
    $oldAccessToken = $loginResponse->json('data.access_token');

    // Use refresh token to get new pair
    $response = $this->postJson('/api/v1/refresh-token', [
        'refresh_token' => $refreshToken,
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'status_code',
            'message',
            'data' => [
                'access_token',
                'refresh_token',
                'token_type',
                'expires_in',
            ],
        ])
        ->assertJsonPath('success', true);

    // New tokens should be different from old ones
    $newAccessToken = $response->json('data.access_token');
    $newRefreshToken = $response->json('data.refresh_token');

    expect($newAccessToken)->not->toBe($oldAccessToken);
    expect($newRefreshToken)->not->toBe($refreshToken);
});

it('cannot refresh with access token', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $loginResponse = $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $accessToken = $loginResponse->json('data.access_token');

    // Try to use access token as refresh token — should fail
    $response = $this->postJson('/api/v1/refresh-token', [
        'refresh_token' => $accessToken,
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('success', false);
});

it('cannot use expired refresh token', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    // Create a refresh token that's already expired
    $token = $user->createToken('refresh-token', ['refresh'], now()->subDay());

    $response = $this->postJson('/api/v1/refresh-token', [
        'refresh_token' => $token->plainTextToken,
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Refresh token has expired.');
});

/*
|--------------------------------------------------------------------------
| Authenticated User Tests
|--------------------------------------------------------------------------
*/

it('can get authenticated user profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/me');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.email', $user->email);
});

it('can logout', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    // Login first to create tokens
    $loginResponse = $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $accessToken = $loginResponse->json('data.access_token');
    $refreshToken = $loginResponse->json('data.refresh_token');

    // Logout using the access token and passing refresh token in body
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $accessToken,
    ])->postJson('/api/v1/logout', [
                'refresh_token' => $refreshToken
            ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Logged out successfully.');

    // Verify all tokens are revoked
    expect($user->tokens()->count())->toBe(0);
});

it('returns 401 for unauthenticated requests', function () {
    $response = $this->getJson('/api/v1/me');

    $response->assertUnauthorized();
});

/*
|--------------------------------------------------------------------------
| Role-Based Access Tests
|--------------------------------------------------------------------------
*/

it('admin can access user list', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/users');

    $response->assertOk()
        ->assertJsonPath('success', true);
});

it('regular user cannot access user list', function () {
    $user = User::factory()->create(); // default role is 'user'

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/users');

    $response->assertForbidden()
        ->assertJsonPath('success', false);
});

it('architect cannot access user list', function () {
    $architect = User::factory()->architect()->create();

    $response = $this->actingAs($architect, 'sanctum')
        ->getJson('/api/v1/users');

    $response->assertForbidden()
        ->assertJsonPath('success', false);
});
