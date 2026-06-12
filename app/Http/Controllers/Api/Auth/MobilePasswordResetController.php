<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Mail\MobileResetPasswordOtpMail;
use App\Models\MobilePasswordResetOtp;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use OpenApi\Annotations as OA;

class MobilePasswordResetController extends Controller
{
    private const OTP_EXPIRES_IN_MINUTES = 10;

    /**
     * Request mobile reset password OTP.
     *
     * @OA\Post(
     *   path="/auth/mobile/password/request-otp",
     *   tags={"Auth"},
     *   summary="Request mobile reset password OTP",
     *   description="Generates a 4-digit OTP for mobile password reset and sends it to the user's email.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       required={"email"},
     *
     *       @OA\Property(property="email", type="string", format="email", example="user@halositek.com")
     *     )
     *   ),
     *
     *   @OA\Response(response=200, description="OTP sent successfully",
     *
     *     @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Kode OTP reset password telah dikirim ke email.", "data": {"expires_in_minutes": 10}})
     *   ),
     *
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function requestOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower((string) $validated['email']);
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return ApiResponse::notFound('Email tidak ditemukan.');
        }

        MobilePasswordResetOtp::query()
            ->where('email', $email)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $otp = (string) random_int(1000, 9999);

        MobilePasswordResetOtp::create([
            'email' => $email,
            'otp' => Hash::make($otp),
            'expires_at' => now()->addMinutes(self::OTP_EXPIRES_IN_MINUTES),
        ]);

        Mail::to($email)->send(new MobileResetPasswordOtpMail(
            otp: $otp,
            userName: $user->name,
            expiresInMinutes: self::OTP_EXPIRES_IN_MINUTES,
        ));

        return ApiResponse::success([
            'expires_in_minutes' => self::OTP_EXPIRES_IN_MINUTES,
        ], 'Kode OTP reset password telah dikirim ke email.');
    }

    /**
     * Verify mobile reset password OTP.
     *
     * @OA\Post(
     *   path="/auth/mobile/password/verify-otp",
     *   tags={"Auth"},
     *   summary="Verify mobile reset password OTP",
     *   description="Validates a 4-digit mobile reset password OTP before the password is updated.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       required={"email","otp"},
     *
     *       @OA\Property(property="email", type="string", format="email", example="user@halositek.com"),
     *       @OA\Property(property="otp", type="string", example="1234")
     *     )
     *   ),
     *
     *   @OA\Response(response=200, description="OTP valid",
     *
     *     @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Kode OTP valid."})
     *   ),
     *
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:4'],
        ]);

        $email = strtolower((string) $validated['email']);

        if (! User::query()->where('email', $email)->exists()) {
            return ApiResponse::notFound('Email tidak ditemukan.');
        }

        $resetOtp = $this->findOtp($email, (string) $validated['otp']);

        if (! $resetOtp) {
            return ApiResponse::validationError([
                'otp' => ['Kode OTP salah.'],
            ], 'Kode OTP salah.');
        }

        if ($resetOtp->isUsed()) {
            return ApiResponse::validationError([
                'otp' => ['Kode OTP sudah digunakan.'],
            ], 'Kode OTP sudah digunakan.');
        }

        if ($resetOtp->isExpired()) {
            return ApiResponse::validationError([
                'otp' => ['Kode OTP sudah expired.'],
            ], 'Kode OTP sudah expired.');
        }

        $resetOtp->forceFill(['verified_at' => now()])->save();

        return ApiResponse::success(message: 'Kode OTP valid.');
    }

    /**
     * Reset mobile password using OTP.
     *
     * @OA\Post(
     *   path="/auth/mobile/password/reset",
     *   tags={"Auth"},
     *   summary="Reset mobile password using OTP",
     *   description="Updates the user's password after validating a 4-digit OTP. The OTP is marked as used after a successful reset.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       required={"email","otp","password","password_confirmation"},
     *
     *       @OA\Property(property="email", type="string", format="email", example="user@halositek.com"),
     *       @OA\Property(property="otp", type="string", example="1234"),
     *       @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *       @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123")
     *     )
     *   ),
     *
     *   @OA\Response(response=200, description="Password reset successful",
     *
     *     @OA\JsonContent(example={"success": true, "status_code": 200, "message": "Password berhasil direset."})
     *   ),
     *
     *   @OA\Response(response=404, ref="#/components/responses/NotFoundError"),
     *   @OA\Response(response=422, ref="#/components/responses/ValidationError"),
     *   @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:4'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = strtolower((string) $validated['email']);
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return ApiResponse::notFound('Email tidak ditemukan.');
        }

        $resetOtp = $this->findOtp($email, (string) $validated['otp']);

        if (! $resetOtp) {
            return ApiResponse::validationError([
                'otp' => ['Kode OTP salah.'],
            ], 'Kode OTP salah.');
        }

        if ($resetOtp->isUsed()) {
            return ApiResponse::validationError([
                'otp' => ['Kode OTP sudah digunakan.'],
            ], 'Kode OTP sudah digunakan.');
        }

        if ($resetOtp->isExpired()) {
            return ApiResponse::validationError([
                'otp' => ['Kode OTP sudah expired.'],
            ], 'Kode OTP sudah expired.');
        }

        $user->password = (string) $validated['password'];
        $user->save();

        $resetOtp->forceFill([
            'verified_at' => $resetOtp->verified_at ?? now(),
            'used_at' => now(),
        ])->save();

        return ApiResponse::success(message: 'Password berhasil direset.');
    }

    private function findOtp(string $email, string $otp): ?MobilePasswordResetOtp
    {
        return MobilePasswordResetOtp::query()
            ->where('email', $email)
            ->latest()
            ->get()
            ->first(function (MobilePasswordResetOtp $resetOtp) use ($otp): bool {
                return Hash::check($otp, (string) $resetOtp->otp);
            });
    }
}
