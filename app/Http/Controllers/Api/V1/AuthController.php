<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\RegisterUserAction;
use App\DTOs\Auth\RegisterUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RefreshTokenRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Responses\ApiResponse;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @OA\Post(
     *   path="/auth/register",
     *   tags={"Auth"},
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       required={"name","email","password","password_confirmation"},
     *
     *       @OA\Property(property="name", type="string", example="Budi Santoso"),
     *       @OA\Property(property="email", type="string", format="email", example="budi_santoso@gmail.com"),
     *       @OA\Property(property="password", type="string", format="password", example="password123"),
     *       @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *       @OA\Property(property="role", type="string", example="user")
     *     )
     *   ),
     *
     *   @OA\Response(response=201, description="Berhasil registrasi")
     * )
     */
    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $dto = RegisterUserDTO::fromRequest($request);
        $user = $action->execute($dto);

        $tokens = $this->issueTokenPair($user);

        return ApiResponse::created([
            ...$user->toArray(),
            ...$tokens,
        ], 'Registration successful.');
    }

    /**
     * Authenticate user and return tokens.
     *
     * @OA\Post(
     *   path="/auth/login",
     *   tags={"Auth"},
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       required={"email","password"},
     *
     *       @OA\Property(property="email", type="string", format="email", example="user@halositek.com"),
     *       @OA\Property(property="password", type="string", example="securepassword123")
     *     )
     *   ),
     *
     *   @OA\Response(response=200, description="Berhasil login")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return ApiResponse::unauthorized('The provided credentials are incorrect.');
        }

        $tokens = $this->issueTokenPair($user);

        return ApiResponse::success([
            ...$user->toArray(),
            ...$tokens,
        ], 'Login successful.');
    }

    /**
     * Refresh access token using a refresh token.
     *
     * @OA\Post(
     *   path="/auth/refresh-token",
     *   tags={"Auth"},
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *
     *       @OA\Property(property="refresh_token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
     *     )
     *   ),
     *
     *   @OA\Response(response=200, description="Berhasil mendapatkan token baru")
     * )
     */
    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $refreshToken = $request->validated('refresh_token');

        // Sanctum tokens are stored as "id|plaintext" â€” extract both parts
        $parts = explode('|', $refreshToken, 2);

        if (count($parts) !== 2) {
            return ApiResponse::unauthorized('Invalid refresh token format.');
        }

        [$tokenId, $plainToken] = $parts;

        // Find the token record in the database
        $tokenRecord = PersonalAccessToken::find($tokenId);

        if (! $tokenRecord) {
            return ApiResponse::unauthorized('Refresh token not found.');
        }

        // Validate: must be a refresh token, not expired, and hash must match
        if ($tokenRecord->name !== 'refresh-token') {
            return ApiResponse::unauthorized('Invalid token type.');
        }

        if ($tokenRecord->expires_at && $tokenRecord->expires_at->isPast()) {
            $tokenRecord->delete();

            return ApiResponse::unauthorized('Refresh token has expired.');
        }

        if (! hash_equals($tokenRecord->token, hash('sha256', $plainToken))) {
            return ApiResponse::unauthorized('Invalid refresh token.');
        }

        /** @var User $user */
        $user = $tokenRecord->tokenable;

        // Revoke all existing tokens for this user (both access and refresh)
        $user->tokens()->delete();

        // Issue a new token pair
        $tokens = $this->issueTokenPair($user);

        return ApiResponse::success($tokens, 'Token refreshed successfully.');
    }

    /**
     * Logout current user and revoke tokens.
     *
     * @OA\Post(
     *   path="/auth/logout",
     *   tags={"Auth"},
     *   security={{"BearerAuth":{}}},
     *
     *   @OA\RequestBody(
     *     required=false,
     *
     *     @OA\JsonContent(
     *
     *       @OA\Property(property="refresh_token", type="string")
     *     )
     *   ),
     *
     *   @OA\Response(response=200, description="Berhasil logout")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        // 1. Revoke the current access token (used in the Authorization header)
        if ($request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        // 2. Revoke the specific refresh token if provided in the body
        if ($request->has('refresh_token')) {
            $parts = explode('|', $request->input('refresh_token'), 2);
            if (count($parts) === 2) {
                PersonalAccessToken::where('_id', $parts[0])->delete();
            }
        }

        return ApiResponse::success(message: 'Logged out successfully.');
    }

    /**
     * Get current authenticated user's profile.
     *
     * @OA\Get(
     *   path="/users/me",
     *   tags={"Users"},
     *   security={{"BearerAuth":{}}},
     *
     *   @OA\Response(response=200, description="Data profil berhasil diambil")
     * )
     */
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success($request->user());
    }

    /**
     * Issue an access token + refresh token pair for a user.
     *
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    private function issueTokenPair(User $user): array
    {
        // Access token: short-lived (60 minutes), full abilities
        $accessToken = $user->createToken(
            'access-token',
            ['*'],
            now()->addMinutes(60)
        );

        // Refresh token: long-lived (30 days), limited to refresh ability only
        $refreshToken = $user->createToken(
            'refresh-token',
            ['refresh'],
            now()->addDays(30)
        );

        return [
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600, // seconds
        ];
    }
}
