<?php

use App\Http\Controllers\Web\Admin\AiBotsController;
use App\Http\Controllers\Web\Admin\ArchitectController;
use App\Http\Controllers\Web\Admin\AuthController;
use App\Http\Controllers\Web\Admin\ConsultationsController;
use App\Http\Controllers\Web\Admin\DashboardController;
use App\Http\Controllers\Web\Admin\DesignController;
use App\Http\Controllers\Web\Admin\SystemAdminController;
use App\Http\Controllers\Web\Admin\UserController;
use App\Http\Controllers\Web\Client\ClientController;
use App\Http\Middleware\EnsureAdminLoginIsActive;
use Illuminate\Support\Facades\Route;

Route::controller(ClientController::class)->group(function () {
    Route::get('/', 'home')->name('client.home');
    Route::get('/about', 'about')->name('client.about');
    Route::get('/download', 'download')->name('client.download');
});

// Auth Pages
Route::prefix('auth')->middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.auth.login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware(EnsureAdminLoginIsActive::class)
        ->name('admin.auth.login.submit');
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
    Route::prefix('consultations')->group(function () {
        Route::get('/', [ConsultationsController::class, 'index'])->name('admin.dashboard.consultations.index');
        Route::get('/report-data', [ConsultationsController::class, 'reportData'])->name('admin.dashboard.consultations.report-data');
        Route::get('/payroll-data', [ConsultationsController::class, 'payrollData'])->name('admin.dashboard.consultations.payroll-data');
    });
    Route::prefix('ai-bots')->group(function () {
        Route::get('/', [AiBotsController::class, 'index'])->name('admin.dashboard.ai-bots.index');
        Route::get('/logs-data', [AiBotsController::class, 'logsData'])->name('admin.dashboard.ai-bots.logs-data');
    });
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.dashboard.logout');

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('admin.dashboard.users.index');
        Route::get('/data', [UserController::class, 'data'])->name('admin.dashboard.users.data');
        Route::put('/{user}', [UserController::class, 'update'])->name('admin.dashboard.users.update');
    });

    Route::prefix('admins')->group(function () {
        Route::get('/', [SystemAdminController::class, 'index'])->name('admin.dashboard.admins.index');
        Route::get('/data', [SystemAdminController::class, 'data'])->name('admin.dashboard.admins.data');
        Route::post('/', [SystemAdminController::class, 'store'])->name('admin.dashboard.admins.store');
        Route::put('/{user}', [SystemAdminController::class, 'update'])->name('admin.dashboard.admins.update');
    });

    Route::prefix('architects')->group(function () {
        Route::get('/', [ArchitectController::class, 'index'])->name('admin.dashboard.architects.index');
        Route::get('/awards', [ArchitectController::class, 'awards'])->name('admin.dashboard.architects.awards');
        Route::get('/stats', [ArchitectController::class, 'stats'])->name('admin.dashboard.architects.stats');
        Route::put('/designs/{project}/status', [ArchitectController::class, 'updateDesignStatus'])->name('admin.dashboard.architects.update-design-status');
        Route::put('/awards/{award}/status', [ArchitectController::class, 'updateAwardStatus'])->name('admin.dashboard.architects.update-award-status');
    });
});
