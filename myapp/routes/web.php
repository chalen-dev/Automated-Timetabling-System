<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;


// Default Page, either Homepage or Dashboard, depending on authentication status
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// Register
Route::get('/register', [AuthController::class, 'showRegisterForm'])
    ->middleware('guest')
    ->name('register.form');

Route::post('/register', [AuthController::class, 'register'])
    ->middleware('guest')
    ->name('register');

// Login
Route::get('/login', [AuthController::class, 'showLoginForm'])
    ->middleware('guest')
    ->name('login.form');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('guest')
    ->name('login');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

//Dashboard
Route::get('/dashboard', [DashboardController::class, 'showDashboard'])
    ->middleware('auth')
    ->name('pages.dashboard');
