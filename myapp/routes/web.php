<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WelcomeController;
use App\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;


// Default Page, either Homepage or Dashboard, depending on authentication status
Route::get('/', [WelcomeController::class, 'index'])
    ->name('default');

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
Route::middleware([Authenticate::class])->group(function () {

    //Logout
    Route::post('/logout', [UserController::class, 'logout'])
        ->name('logout');

    //Dashboard Routes
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard.index');

    //Courses Routes
    Route::get('/courses', [CourseController::class, 'index'])
        ->name('courses.index');
    Route::get('/courses/{id}', [CourseController::class, 'show'])
        ->name('courses.show');

    //Sessions Routes
    Route::get('/ClassSections', [SessionController::class, 'index'])
        ->name('ClassSections.index');
    Route::get('ClassSections/{id}', [SessionController::class, 'show'])
        ->name('ClassSections.show');
});



