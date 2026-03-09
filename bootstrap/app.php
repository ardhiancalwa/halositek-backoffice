<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);

        $middleware->api(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Enforce consistent JSON structure for ALL API errors & exceptions
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {

                // 1. Validation Error
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return ApiResponse::validationError($e->errors(), $e->getMessage());
                }

                // 2. Authentication Error (Not logged in / invalid token)
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return ApiResponse::unauthorized($e->getMessage());
                }

                // 3. Authorization Error (Forbidden / Gates)
                if (
                    $e instanceof \Illuminate\Auth\Access\AuthorizationException ||
                    $e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
                ) {
                    return ApiResponse::forbidden($e->getMessage());
                }

                // 4. Model/Route Not Found
                if (
                    $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ||
                    $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
                ) {
                    return ApiResponse::notFound('Endpoint or resource not found.');
                }

                // 5. General HTTP Exceptions (e.g. 405 Method Not Allowed)
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                    $status = \App\Enums\ApiStatus::tryFrom($e->getStatusCode()) ?? \App\Enums\ApiStatus::SERVER_ERROR;
                    $message = $e->getMessage() !== '' ? $e->getMessage() : null;
                    return ApiResponse::error($message, $status);
                }

                // 6. Generic/Fatal Exceptions (500)
                $message = config('app.debug') ? $e->getMessage() : null;
                return ApiResponse::error($message, \App\Enums\ApiStatus::SERVER_ERROR);
            }
        });
    })->create();
