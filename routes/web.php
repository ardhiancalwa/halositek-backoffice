<?php

use App\Http\Controllers\Web\Admin\AiBotsController;
use App\Http\Controllers\Web\Admin\ArchitectController;
use App\Http\Controllers\Web\Admin\AuthController;
use App\Http\Controllers\Web\Admin\ConsultationsController;
use App\Http\Controllers\Web\Admin\DashboardController;
use App\Http\Controllers\Web\Admin\DesignController;
use App\Http\Controllers\Web\Admin\UserController;
use App\Http\Controllers\Web\Client\ClientController;
use Illuminate\Support\Facades\Route;

Route::controller(ClientController::class)->group(function () {
    Route::get('/', 'home')->name('client.home');
    Route::get('/about', 'about')->name('client.about');
    Route::get('/download', 'download')->name('client.download');
});

// Auth Pages
Route::prefix('auth')->middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.auth.login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.auth.login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('admin.auth.register');
});

// Dashboard Pages
Route::prefix('dashboard')->middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard.index');
    Route::get('/stats', [DashboardController::class, 'dashboardStats'])->name('admin.dashboard.stats');
    Route::get('/user-growth', [DashboardController::class, 'userGrowth'])->name('admin.dashboard.user-growth');
    Route::get('/architect-growth', [DashboardController::class, 'architectGrowth'])->name('admin.dashboard.architect-growth');

    Route::get('/designs', [DesignController::class, 'index'])->name('admin.dashboard.designs.index');
    Route::get('/designs/{project}', [DesignController::class, 'show'])->name('admin.dashboard.designs.show');
    Route::put('/projects/{project}', [DesignController::class, 'update'])->name('admin.projects.update');
    Route::delete('/projects/{project}', [DesignController::class, 'destroy'])->name('admin.projects.destroy');
    Route::get('/consultations', [ConsultationsController::class, 'index'])->name('admin.dashboard.consultations.index');
    Route::get('/ai-bots', [AiBotsController::class, 'index'])->name('admin.dashboard.ai-bots.index');
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.dashboard.logout');

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('admin.dashboard.users.index');
        Route::get('/data', [UserController::class, 'data'])->name('admin.dashboard.users.data');
    });

    Route::prefix('architects')->group(function () {
        Route::get('/', [ArchitectController::class, 'index'])->name('admin.dashboard.architects.index');
        Route::get('/awards', [ArchitectController::class, 'awards'])->name('admin.dashboard.architects.awards');
        Route::get('/stats', [ArchitectController::class, 'stats'])->name('admin.dashboard.architects.stats');
        Route::put('/designs/{project}/status', [ArchitectController::class, 'updateDesignStatus'])->name('admin.dashboard.architects.update-design-status');
    });
});
