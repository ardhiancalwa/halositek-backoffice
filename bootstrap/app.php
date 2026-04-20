<?php

use App\Enums\ApiStatus;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        channels: __DIR__ . '/../routes/channels.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);

        $middleware->statefulApi();

        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Enforce consistent JSON structure for ALL API errors & exceptions
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {

                // 1. Validation Error
                if ($e instanceof ValidationException) {
                    return ApiResponse::validationError($e->errors(), 'Validasi gagal.');
                }

                // 2. Authentication Error (Not logged in / invalid token)
                if ($e instanceof AuthenticationException) {
                    return ApiResponse::unauthorized($e->getMessage());
                }

                // 3. Authorization Error (Forbidden / Gates)
                if ($e instanceof AuthorizationException) {
                    return ApiResponse::forbidden('Kamu tidak memiliki akses untuk melakukan tindakan ini.');
                }

                if ($e instanceof AccessDeniedHttpException) {
                    return ApiResponse::forbidden($e->getMessage());
                }

                // 4. Model/Route Not Found
                if ($e instanceof ModelNotFoundException) {
                    return ApiResponse::notFound('Data tidak ditemukan.');
                }

                if ($e instanceof NotFoundHttpException) {
                    return ApiResponse::notFound('Endpoint or resource not found.');
                }

                // 4.5. Query Exception (Mongodb duplicate keys, etc)
                if ($e instanceof QueryException || str_contains($e::class, 'BulkWriteException')) {
                    $msg = $e->getMessage();
                    if (str_contains($msg, '1062') || str_contains($msg, 'E11000')) {
                        return ApiResponse::error('Aksi sudah pernah dilakukan sebelumnya.', ApiStatus::CONFLICT);
                    }
                }

                // 5. General HTTP Exceptions (e.g. 405 Method Not Allowed)
                if ($e instanceof HttpException) {
                    $status = ApiStatus::tryFrom($e->getStatusCode()) ?? ApiStatus::SERVER_ERROR;
                    $message = $e->getMessage() !== '' ? $e->getMessage() : null;

                    return ApiResponse::error($message, $status);
                }

                // 6. Generic/Fatal Exceptions (500)
                $message = config('app.debug') ? $e->getMessage() : null;

                return ApiResponse::error($message, ApiStatus::SERVER_ERROR);
            }
        });
    })->create();
