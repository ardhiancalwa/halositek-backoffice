<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\ArchitectController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::get('/dashboard', [AuthController::class, 'showDashboard'])->name('dashboard');
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/architects', [ArchitectController::class, 'index'])->name('architects.index');
Route::get('/designs', [AuthController::class, 'showDesigns'])->name('designs.index');
Route::get('/consultations', [AuthController::class, 'showConsultations'])->name('consultations.index');
