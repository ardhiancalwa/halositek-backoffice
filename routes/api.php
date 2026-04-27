<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ArchitectController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AwardController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\UserController;
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
        Route::post('/me', [UserController::class, 'updateProfile']);

        Route::prefix('/chat')->group(function () {
            Route::get('/conversations', [ConversationController::class, 'index']);
            Route::post('/conversations', [ConversationController::class, 'store']);
            Route::get('/conversations/{conversationId}', [ConversationController::class, 'show']);

            Route::get('/conversations/{conversationId}/messages', [MessageController::class, 'index']);
            Route::post('/messages', [MessageController::class, 'store']);
            Route::post('/conversations/{conversationId}/read', [MessageController::class, 'markAsRead']);
            Route::post('/conversations/{conversationId}/typing', [MessageController::class, 'typing']);
        });

        // Project CRUD (Architect/Admin)
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::put('/projects/{id}', [ProjectController::class, 'update']);
        Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);

        // Project interactions
        Route::post('/projects/{id}/like', [ProjectController::class, 'like']);
        Route::delete('/projects/{id}/like', [ProjectController::class, 'unlike']);

        // Award CRUD (Architect/Admin)
        Route::post('/awards', [AwardController::class, 'store']);
        Route::put('/awards/{id}', [AwardController::class, 'update']);
        Route::delete('/awards/{id}', [AwardController::class, 'destroy']);

        // Architect wishlist
        Route::get('/architects/wishlist', [ArchitectController::class, 'wishlist']);
        Route::post('/architects/{userId}/save', [ArchitectController::class, 'save']);
        Route::delete('/architects/{userId}/save', [ArchitectController::class, 'unsave']);
    });

    // Admin only routes
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/analytics/overview', [AnalyticsController::class, 'overview']);
        Route::get('/analytics/user-growth', [AnalyticsController::class, 'userGrowth']);
        Route::get('/analytics/architect-growth', [AnalyticsController::class, 'architectGrowth']);

        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);

        // Architect review helpers
        Route::get('/architects/unapproved', [ArchitectController::class, 'unapproved']);
        Route::put('/architects/{userId}/verify', [ArchitectController::class, 'verify']);

        // Approval endpoints
        Route::put('/awards/{id}/approve', [AwardController::class, 'approve']);
        Route::put('/projects/{id}/approve', [ProjectController::class, 'approve']);

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
