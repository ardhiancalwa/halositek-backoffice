<?php

use App\Mail\MobileResetPasswordOtpMail;
use App\Models\ArchitectProfile;
use App\Models\MobilePasswordResetOtp;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

afterEach(function () {
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('architect_profiles')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
    DB::connection('mongodb')->table('mobile_password_reset_otps')->delete();
});

/*
|--------------------------------------------------------------------------
| Register Tests
|--------------------------------------------------------------------------
*/

it('can register as user', function () {
    $response = $this->postJson('/api/v1/auth/register', [
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
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Architect User',
        'email' => 'architect@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'architect',
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.role', 'architect');

    $user = User::where('email', 'architect@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->role->value)->toBe('architect');

    // Assert that the architect profile was automatically created and approved
    $profile = ArchitectProfile::where('user_id', $user->id)->first();
    expect($profile)->not->toBeNull();
    expect($profile->status)->toBe('approved');

    // Assert that they appear in the public architect list
    $listResponse = $this->getJson('/api/v1/architects');
    $listResponse->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.0.id', $user->id);
});

it('cannot register as admin', function () {
    $response = $this->postJson('/api/v1/auth/register', [
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

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Duplicate User',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false);
});

it('cannot register without password confirmation', function () {
    $response = $this->postJson('/api/v1/auth/register', [
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

    $response = $this->postJson('/api/v1/auth/login', [
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

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'login@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'The provided credentials are incorrect.');
});

/*
|--------------------------------------------------------------------------
| Mobile Password Reset Tests
|--------------------------------------------------------------------------
*/

it('can request mobile password reset otp', function () {
    Mail::fake();

    User::factory()->create([
        'name' => 'Mobile User',
        'email' => 'mobile@example.com',
    ]);

    $response = $this->postJson('/api/v1/auth/mobile/password/request-otp', [
        'email' => 'mobile@example.com',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Kode OTP reset password telah dikirim ke email.')
        ->assertJsonPath('data.expires_in_minutes', 10);

    $record = MobilePasswordResetOtp::query()->where('email', 'mobile@example.com')->first();
    expect($record)->not->toBeNull();
    expect($record->otp)->not->toBeNull();
    expect($record->expires_at)->not->toBeNull();

    Mail::assertSent(MobileResetPasswordOtpMail::class, function (MobileResetPasswordOtpMail $mail): bool {
        return $mail->hasTo('mobile@example.com')
            && strlen($mail->otp) === 4
            && $mail->expiresInMinutes === 10;
    });
});

it('returns not found when requesting mobile reset otp for unknown email', function () {
    $response = $this->postJson('/api/v1/auth/mobile/password/request-otp', [
        'email' => 'missing@example.com',
    ]);

    $response->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Email tidak ditemukan.');
});

it('can verify mobile reset otp', function () {
    $otp = '1234';

    User::factory()->create(['email' => 'mobile@example.com']);
    MobilePasswordResetOtp::create([
        'email' => 'mobile@example.com',
        'otp' => Hash::make($otp),
        'expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->postJson('/api/v1/auth/mobile/password/verify-otp', [
        'email' => 'mobile@example.com',
        'otp' => $otp,
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Kode OTP valid.');

    expect(MobilePasswordResetOtp::query()->where('email', 'mobile@example.com')->first()->verified_at)
        ->not->toBeNull();
});

it('rejects wrong mobile reset otp', function () {
    User::factory()->create(['email' => 'mobile@example.com']);
    MobilePasswordResetOtp::create([
        'email' => 'mobile@example.com',
        'otp' => Hash::make('1234'),
        'expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->postJson('/api/v1/auth/mobile/password/verify-otp', [
        'email' => 'mobile@example.com',
        'otp' => '4321',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Kode OTP salah.')
        ->assertJsonPath('errors.otp.0', 'Kode OTP salah.');
});

it('rejects expired mobile reset otp', function () {
    $otp = '1234';

    User::factory()->create(['email' => 'mobile@example.com']);
    MobilePasswordResetOtp::create([
        'email' => 'mobile@example.com',
        'otp' => Hash::make($otp),
        'expires_at' => now()->subMinute(),
    ]);

    $response = $this->postJson('/api/v1/auth/mobile/password/verify-otp', [
        'email' => 'mobile@example.com',
        'otp' => $otp,
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Kode OTP sudah expired.')
        ->assertJsonPath('errors.otp.0', 'Kode OTP sudah expired.');
});

it('can reset password with valid mobile otp and marks otp as used', function () {
    $otp = '1234';
    $user = User::factory()->create([
        'email' => 'mobile@example.com',
        'password' => bcrypt('old-password'),
    ]);

    MobilePasswordResetOtp::create([
        'email' => 'mobile@example.com',
        'otp' => Hash::make($otp),
        'expires_at' => now()->addMinutes(10),
        'verified_at' => now(),
    ]);

    $response = $this->postJson('/api/v1/auth/mobile/password/reset', [
        'email' => 'mobile@example.com',
        'otp' => $otp,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Password berhasil direset.');

    $user->refresh();
    expect(Hash::check('new-password', $user->password))->toBeTrue();
    expect(MobilePasswordResetOtp::query()->where('email', 'mobile@example.com')->first()->used_at)
        ->not->toBeNull();
});

it('rejects used mobile reset otp', function () {
    $otp = '1234';

    User::factory()->create(['email' => 'mobile@example.com']);
    MobilePasswordResetOtp::create([
        'email' => 'mobile@example.com',
        'otp' => Hash::make($otp),
        'expires_at' => now()->addMinutes(10),
        'used_at' => now(),
    ]);

    $response = $this->postJson('/api/v1/auth/mobile/password/reset', [
        'email' => 'mobile@example.com',
        'otp' => $otp,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Kode OTP sudah digunakan.')
        ->assertJsonPath('errors.otp.0', 'Kode OTP sudah digunakan.');
});

it('rejects mobile password reset when password confirmation does not match', function () {
    User::factory()->create(['email' => 'mobile@example.com']);

    $response = $this->postJson('/api/v1/auth/mobile/password/reset', [
        'email' => 'mobile@example.com',
        'otp' => '1234',
        'password' => 'new-password',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('errors.password.0', 'The password field confirmation does not match.');
});

/*
|--------------------------------------------------------------------------
| Refresh Token Tests
|--------------------------------------------------------------------------
*/

it('can refresh token', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    // Login to get tokens
    $loginResponse = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $refreshToken = $loginResponse->json('data.refresh_token');
    $oldAccessToken = $loginResponse->json('data.access_token');

    // Use refresh token to get new pair
    $response = $this->postJson('/api/v1/auth/refresh-token', [
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

    $loginResponse = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $accessToken = $loginResponse->json('data.access_token');

    // Try to use access token as refresh token — should fail
    $response = $this->postJson('/api/v1/auth/refresh-token', [
        'refresh_token' => $accessToken,
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('success', false);
});

it('cannot use expired refresh token', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    // Create a refresh token that's already expired
    $token = $user->createToken('refresh-token', ['refresh'], now()->subDay());

    $response = $this->postJson('/api/v1/auth/refresh-token', [
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
    $loginResponse = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $accessToken = $loginResponse->json('data.access_token');
    $refreshToken = $loginResponse->json('data.refresh_token');

    // Logout using the access token and passing refresh token in body
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $accessToken,
    ])->postJson('/api/v1/logout', [
        'refresh_token' => $refreshToken,
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Logged out successfully.');

    // Verify all tokens are revoked
    expect($user->tokens()->count())->toBe(0);
});

it('can change password with current password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('old-password'),
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/auth/change-password', [
            'current_password' => 'old-password',
            'new_password' => 'new-password',
            'new_password_confirmation' => 'new-password',
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Password berhasil diubah.');

    $user->refresh();
    expect(Hash::check('new-password', $user->password))->toBeTrue();
});

it('rejects change password when current password is wrong', function () {
    $user = User::factory()->create([
        'password' => bcrypt('old-password'),
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/auth/change-password', [
            'current_password' => 'wrong-password',
            'new_password' => 'new-password',
            'new_password_confirmation' => 'new-password',
        ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Password saat ini tidak sesuai.')
        ->assertJsonPath('errors.current_password.0', 'Password saat ini tidak sesuai.');

    $user->refresh();
    expect(Hash::check('old-password', $user->password))->toBeTrue();
});

it('rejects change password when confirmation does not match', function () {
    $user = User::factory()->create([
        'password' => bcrypt('old-password'),
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/auth/change-password', [
            'current_password' => 'old-password',
            'new_password' => 'new-password',
            'new_password_confirmation' => 'different-password',
        ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('errors.new_password.0', 'The new password field confirmation does not match.');
});

it('requires authentication to change password', function () {
    $response = $this->postJson('/api/v1/auth/change-password', [
        'current_password' => 'old-password',
        'new_password' => 'new-password',
        'new_password_confirmation' => 'new-password',
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('success', false);
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
