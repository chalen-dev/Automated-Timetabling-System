<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;


// Default Page, either Homepage or Dashboard, depending on authentication status
Route::get('/', [WelcomeController::class, 'index'])
    ->name('welcome');

// Guest Routes (Unauthenticated)
Route::middleware('guest')->group(function () {
    // Register
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register.form');
    Route::post('/register', [AuthController::class, 'register'])->name('register');

    // Login
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    //Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    //Dashboard
    Route::get('/dashboard', [DashboardController::class, 'showDashboard'])->name('dashboard.index');

});



