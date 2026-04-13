<?php

use App\Http\Controllers\Web\AuthController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::get('/dashboard', [AuthController::class, 'showDashboard'])->name('dashboard');
Route::get('/designs', [AuthController::class, 'showDesigns'])->name('designs.index');
Route::get('/consultations', [AuthController::class, 'showConsultations'])->name('consultations.index');
