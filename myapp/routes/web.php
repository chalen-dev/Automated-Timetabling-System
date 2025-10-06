<?php


use App\Http\Controllers\AcademicProgramController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseSessionController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomExclusiveDayController;
use App\Http\Controllers\SessionGroupController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\TableFillController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\TimetableEditingPaneController;
use App\Http\Controllers\TimetableProfessorController;
use App\Http\Controllers\TimetableRoomController;
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

    //Test (Fill Tables with Values)
    Route::match(['get'], '/fill-table/{table}', [TableFillController::class, 'fill'])->name('table.fill');

    //Logout
    Route::post('/logout', [UserController::class, 'logout'])
        ->name('logout');

    //Timetable List(act as dashboard)
    Route::resource('timetables', TimetableController::class);
    //Timetabling Editing Pane
        Route::resource('timetables.timetable-editing-pane', TimetableEditingPaneController::class)
            ->only('index');
        Route::resource('timetables.session-groups', SessionGroupController::class)
            ->except('show');
            Route::resource('timetables.session-groups.course-sessions', CourseSessionController::class);
             Route::patch('timetables/{timetable}/session-groups/{sessionGroup}/course-sessions/{courseSession}/update-term',
            [CourseSessionController::class, 'updateTerm'])
            ->name('timetables.session-groups.course-sessions.update-term');

        Route::resource('timetables.timetable-professors', TimetableProfessorController::class)
            ->only('index', 'create', 'store', 'destroy');
        Route::resource('timetables.timetable-rooms', TimetableRoomController::class)
            ->only('index', 'create', 'store', 'destroy');

    // Courses Routes
    Route::resource('courses', CourseController::class);

    //Professor List
    Route::resource('professors', ProfessorController::class);
       //Specialization List
       Route::resource('professors.specializations', SpecializationController::class)
           ->only('index', 'create', 'store', 'destroy');

    // Academic Program Routes
    Route::resource('academic-programs', AcademicProgramController::class);

    //Room List
    Route::resource('rooms', RoomController::class);
        //Room Exclusive Day List
        Route::resource('rooms.room-exclusive-days', RoomExclusiveDayController::class)
            ->only('index', 'create', 'store', 'destroy');

});



