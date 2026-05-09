<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showForgotForm(): Factory|View
    {
        return view('admin.pages.auth.forgot-password');
    }

    /**
     * Handle sending the password reset link.
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->input('email');

        // Check if user exists
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return back()
                ->withErrors(['email' => 'We could not find an account with that email address.'])
                ->onlyInput('email');
        }

        // Throttle: check if a token was sent recently (within 60 seconds)
        $recentToken = PasswordResetToken::query()
            ->where('email', $email)
            ->latest()
            ->first();

        if ($recentToken && $recentToken->created_at && $recentToken->created_at->diffInSeconds(now()) < 60) {
            $waitSeconds = 60 - $recentToken->created_at->diffInSeconds(now());

            return back()
                ->withErrors(['email' => "Please wait {$waitSeconds} seconds before requesting another reset link."])
                ->onlyInput('email');
        }

        // Delete old tokens for this email
        PasswordResetToken::query()->where('email', $email)->delete();

        // Generate new token
        $plainToken = Str::random(64);

        PasswordResetToken::create([
            'email' => $email,
            'token' => Hash::make($plainToken),
        ]);

        // Build reset URL
        $resetUrl = route('admin.auth.reset-password', [
            'token' => $plainToken,
            'email' => $email,
        ]);

        // Send email
        Mail::to($email)->send(new ResetPasswordMail(
            resetUrl: $resetUrl,
            userName: $user->name,
        ));

        return back()->with('status', 'We have sent a password reset link to your email address.');
    }

    /**
     * Show the reset password form.
     */
    public function showResetForm(Request $request, string $token): Factory|View
    {
        return view('admin.pages.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Handle the password reset.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = $request->input('email');
        $token = $request->input('token');

        // Find the reset token record
        $resetToken = PasswordResetToken::query()
            ->where('email', $email)
            ->first();

        if (! $resetToken) {
            return back()->withErrors(['email' => 'Invalid or expired password reset link.']);
        }

        // Verify token hash
        if (! Hash::check($token, $resetToken->token)) {
            return back()->withErrors(['email' => 'Invalid or expired password reset link.']);
        }

        // Check expiry (60 minutes)
        if ($resetToken->isExpired(60)) {
            $resetToken->delete();

            return back()->withErrors(['email' => 'This password reset link has expired. Please request a new one.']);
        }

        // Find user and update password
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return back()->withErrors(['email' => 'We could not find an account with that email address.']);
        }

        $user->password = $request->input('password');
        $user->save();

        // Delete the used token
        $resetToken->delete();

        return redirect()
            ->route('admin.auth.login')
            ->with('status', 'Your password has been reset successfully. Please login with your new password.');
    }
}
