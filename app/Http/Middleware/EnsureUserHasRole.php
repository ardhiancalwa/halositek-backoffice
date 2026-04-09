<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::unauthorized();
        }

        $allowedRoles = array_map(
            fn (string $role) => UserRole::tryFrom($role),
            $roles
        );

        foreach ($allowedRoles as $role) {
            if ($role !== null && $user->hasRole($role)) {
                return $next($request);
            }
        }

        return ApiResponse::forbidden('You do not have the required role to access this resource.');
    }
}
