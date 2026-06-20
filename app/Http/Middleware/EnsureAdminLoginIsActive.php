<?php

namespace App\Http\Middleware;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminLoginIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $email = (string) $request->input('email', '');
        $password = (string) $request->input('password', '');

        if ($email === '' || $password === '') {
            return $next($request);
        }

        $user = User::query()
            ->where('email', $email)
            ->where('role', UserRole::Admin->value)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return $next($request);
        }

        if ($user->account_status === AccountStatus::Suspend) {
            return redirect()
                ->back()
                ->withErrors(['email' => 'This admin account has been suspended.'])
                ->onlyInput('email');
        }

        return $next($request);
    }
}
