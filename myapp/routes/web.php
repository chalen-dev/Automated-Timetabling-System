<?php


use App\Http\Controllers\AcademicProgramController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomExclusiveDayController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WelcomeController;
use App\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;


// Default Page, either Homepage or timetables, depending on authentication status
Route::get('/', [WelcomeController::class, 'index'])
    ->name('home');

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

    // Timetable Routes (timetable list, the DASHBOARD)
    Route::resource('timetables', TimetableController::class);

    // courses Routes
    Route::resource('courses', CourseController::class);

    // Professor Routes
    Route::resource('professors', ProfessorController::class);
        // Specialization Routes (scoped to professors)
        Route::resource('professors.specializations', SpecializationController::class)->only('create', 'index', 'store', 'destroy');

    // Academic Program Routes
    Route::resource('academic-programs', AcademicProgramController::class);

    // Room Routes
    Route::resource('rooms', RoomController::class);
        // Room Exclusive Day Routes (scoped to rooms)
        Route::resource('rooms.room-exclusive-days', RoomExclusiveDayController::class)->only('index', 'create', 'store', 'destroy');

});



