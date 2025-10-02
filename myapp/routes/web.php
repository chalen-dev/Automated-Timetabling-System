<?php


use App\Http\Controllers\AcademicProgramController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomExclusiveDayController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\TimetableEditingPaneController;
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

    // Timetable and its nested routes NOTE: the TIMETABLE LIST act as DASHBOARD
    Route::prefix('timetables')->name('timetables.')->group(function() {
            //Timetable List(act as dashboard)
            Route::resource('/', TimetableController::class);
            //Timetabling Editing Pane
            Route::resource('timetable-editing-pane', TimetableEditingPaneController::class)->only('index');
    });

    // Courses Routes
    Route::resource('courses', CourseController::class);

    //Professor and nested routes
    Route::prefix('professors')->name('professors.')->group(function() {
        //Professor List
       Route::resource('/', ProfessorController::class);
       //Specialization List
       Route::resource('specializations', SpecializationController::class)->only('create', 'index', 'store', 'destroy');
    });

    // Academic Program Routes
    Route::resource('academic-programs', AcademicProgramController::class);

    // Room and nested routes
    Route::prefix('rooms')->name('rooms.')->group(function() {
        //Room List
        Route::resource('/', RoomController::class);
        //Room Exclusive Day List
        Route::resource('room-exclusive-days', RoomExclusiveDayController::class)->only('index', 'create', 'store', 'destroy');
    });

});



