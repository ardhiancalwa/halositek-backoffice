<?php

use App\Http\Controllers\Web\Admin\AuthController;
use App\Http\Controllers\Web\Admin\ConsultationsController;
use App\Http\Controllers\Web\Admin\DashboardController;
use App\Http\Controllers\Web\Client\ClientController;
use Illuminate\Support\Facades\Route;

Route::controller(ClientController::class)->group(function () {
    Route::get('/', 'home')->name('client.home');
    Route::get('/about', 'about')->name('client.about');
    Route::get('/download', 'download')->name('client.download');
});

// Auth Pages
Route::prefix('auth')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.auth.login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('admin.auth.register');
});

// Dashboard Pages
Route::prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard.index');
    Route::get('/designs', [AuthController::class, 'showDesigns'])->name('admin.dashboard.designs.index');
    Route::get('/consultations', [ConsultationsController::class, 'index'])->name('admin.dashboard.consultations.index');
});
