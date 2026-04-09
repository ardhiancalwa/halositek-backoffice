<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\CatalogController;
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
        
        // Catalog Routes (Authenticated)
        Route::post('/catalogs/{id}/like', [CatalogController::class, 'like']);
        Route::delete('/catalogs/{id}/like', [CatalogController::class, 'unlike']);
        
        // Catalog CRUD (Architect)
        Route::post('/catalogs', [CatalogController::class, 'store']);
        Route::put('/catalogs/{id}', [CatalogController::class, 'update']);
        Route::delete('/catalogs/{id}', [CatalogController::class, 'destroy']);
    });

    // Admin only routes
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        
        // Catalog Review/Verify
        Route::put('/catalogs/{id}/verify', [CatalogController::class, 'verify']);
    });
    
    // Public routes (down here to avoid intercepting wishlist if grouped)
    // Actually wishlist is above, so we are safe.
    Route::get('/catalogs', [CatalogController::class, 'index']);
    Route::get('/catalogs/{id}', [CatalogController::class, 'show']);
});
