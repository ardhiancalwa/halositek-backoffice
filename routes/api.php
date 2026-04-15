<?php

use App\Http\Controllers\Api\V1\ArchitectController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AwardController;
use App\Http\Controllers\Api\V1\FaqController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/refresh-token', [AuthController::class, 'refresh']);

    // Protected routes (any authenticated user)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/me', [UserController::class, 'updateProfile']);

        // Project CRUD (Architect/Admin)
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::put('/projects/{id}', [ProjectController::class, 'update']);
        Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);

        // Award CRUD (Architect/Admin)
        Route::post('/awards', [AwardController::class, 'store']);
        Route::put('/awards/{id}', [AwardController::class, 'update']);
        Route::delete('/awards/{id}', [AwardController::class, 'destroy']);

        // Architect wishlist
        Route::get('/architects/wishlist', [ArchitectController::class, 'wishlist']);
        Route::post('/architects/{id}/save', [ArchitectController::class, 'save']);
        Route::delete('/architects/{id}/save', [ArchitectController::class, 'unsave']);
    });

    // Admin only routes
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);

        // Architect Review/Verify
        Route::put('/architects/{id}/verify', [ArchitectController::class, 'verify']);

        // FAQ management
        Route::post('/faqs', [FaqController::class, 'store']);
        Route::put('/faqs/{id}', [FaqController::class, 'update']);
        Route::delete('/faqs/{id}', [FaqController::class, 'destroy']);
    });

    // Public routes (down here to avoid intercepting wishlist if grouped)
    // Actually wishlist is above, so we are safe.
    Route::get('/architects', [ArchitectController::class, 'index']);

    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);

    Route::get('/awards', [AwardController::class, 'index']);
    Route::get('/awards/{id}', [AwardController::class, 'show']);

    Route::get('/faqs', [FaqController::class, 'index']);
    Route::get('/faqs/{id}', [FaqController::class, 'show']);
});
