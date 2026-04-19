<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\ArchitectController;
use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'showDashboard'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'dashboardStats'])->name('dashboard.stats');
    Route::get('/dashboard/user-growth', [DashboardController::class, 'userGrowth'])->name('dashboard.user-growth');
    Route::get('/dashboard/architect-growth', [DashboardController::class, 'architectGrowth'])->name('dashboard.architect-growth');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/data', [UserController::class, 'data'])->name('users.data');
    Route::get('/architects', [ArchitectController::class, 'index'])->name('architects.index');
    Route::get('/architects/awards', [ArchitectController::class, 'awards'])->name('architects.awards');
    Route::get('/architects/stats', [ArchitectController::class, 'stats'])->name('architects.stats');
    Route::get('/designs', [DashboardController::class, 'showDesigns'])->name('designs.index');
    Route::get('/consultations', [DashboardController::class, 'showConsultations'])->name('consultations.index');
});
