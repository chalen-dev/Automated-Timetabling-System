<?php

use App\Http\Controllers\AcademicProgramController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseSessionController;
use App\Http\Controllers\GenerateTimetableController;
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
use App\Http\Controllers\UserLogController;
use App\Http\Controllers\WelcomeController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

// Default Page
Route::get('/', [WelcomeController::class, 'index'])->name('home');

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [UserController::class, 'showRegisterForm'])->name('register.form');
    Route::post('/register', [UserController::class, 'register'])->name('register');

    Route::get('/login', [UserController::class, 'showLoginForm'])->name('login.form');
    Route::post('/login', [UserController::class, 'login'])->name('login');
});

// Authenticated Routes
Route::middleware([Authenticate::class])->group(function () {
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');

    // Timetables & related resources (same for admin and normal users)
    Route::resource('timetables', TimetableController::class);
    Route::resource('timetables.timetable-editing-pane', TimetableEditingPaneController::class)->only('index');
    Route::resource('timetables.session-groups', SessionGroupController::class);
    Route::resource('timetables.session-groups.course-sessions', CourseSessionController::class);
    Route::patch(
        'timetables/{timetable}/session-groups/{sessionGroup}/course-sessions/{courseSession}/update-term',
        [CourseSessionController::class, 'updateTerm']
    )->name('timetables.session-groups.course-sessions.update-term');

    Route::resource('timetables.timetable-professors', TimetableProfessorController::class)->only('index','create','store');
        Route::delete('/timetables/{timetable}/timetable-professors/{professor}',
            [TimetableProfessorController::class, 'destroy'])
            ->name('timetables.timetable-professors.destroy');
    Route::resource('timetables.timetable-rooms', TimetableRoomController::class)->only('index','create','store','destroy');

    Route::resource('timetables.generate-timetable', GenerateTimetableController::class);
    Route::get('timetables/{timetable}/generate', [GenerateTimetableController::class, 'index'])->name('timetables.generate');
    Route::post('timetables/{timetable}/generate', [GenerateTimetableController::class, 'generate'])->name('timetables.generate.post');

    // Courses
    Route::resource('courses', CourseController::class);

    // Professors
    Route::resource('professors', ProfessorController::class);
    Route::resource('professors.specializations', SpecializationController::class)->only('index','create','store','destroy');

    // Academic Programs
    Route::resource('academic-programs', AcademicProgramController::class);

    // Rooms
    Route::resource('rooms', RoomController::class);
    Route::resource('rooms.room-exclusive-days', RoomExclusiveDayController::class)->only('index','create','store','destroy');

    // Test route (Tinker)
    Route::get('/test-file', function() {
        $path = base_path("scripts/public/exports/timetables/1.xlsx");
        return file_exists($path) ? "Exists" : "Missing: $path";
    });
});

// Admin-only actions
Route::middleware(['auth', AdminMiddleware::class])->group(function() {
    Route::get('/admin/users', [AdminController::class,'showPending'])->name('admin.pending_users');
    Route::post('/admin/users/{user}/approve', [AdminController::class,'approve'])->name('admin.approve_user');
    Route::post('/admin/users/{id}/toggle-authorize', [AdminController::class, 'toggleAuthorize'])
        ->name('admin.toggle_authorize');
    Route::delete('/admin/users/{id}/decline', [AdminController::class, 'declineUser'])
        ->name('admin.decline_user');

    // User Logs page
    Route::get('/admin/user-logs', [UserLogController::class, 'index'])
        ->name('admin.user-logs');
});
