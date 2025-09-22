<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;


// Default Page, either Homepage or Dashboard, depending on authentication status
Route::get('/', [WelcomeController::class, 'index'])
    ->name('welcome');

// Guest Routes (Unauthenticated)
Route::middleware('guest')->group(function () {
    // Register
    Route::get('/register', [UserController::class, 'showRegisterForm'])
        ->name('register.form');
    Route::post('/register', [UserController::class, 'register'])
        ->name('register');

    // Login
    Route::get('/login', [UserController::class, 'showLoginForm'])
        ->name('login.form');
    Route::post('/login', [UserController::class, 'login'])
        ->name('login');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    //Logout
    Route::post('/logout', [UserController::class, 'logout'])
        ->name('logout');

    //Dashboard
    Route::get('/main-timetable-list', [DashboardController::class, 'showDashboard'])
        ->name('dashboard.main-timetable-list');
    Route::get('/course-list', [DashboardController::class, 'showCourseList'])
        ->name('dashboard.course-list');
    Route::get('/room-list', [DashboardController::class, 'showRoomList'])
        ->name('dashboard.room-list');
    Route::get('/professor-list', [DashboardController::class, 'showProfessorList'])
        ->name('dashboard.professor-list');
});

// Auth routes (login, register, password reset, email verify, etc.)
//Email verification will be implemented later.
//Auth::routes(['verify' => true]);



