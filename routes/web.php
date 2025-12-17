<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Records\AcademicProgramController;
use App\Http\Controllers\Records\CourseAcademicProgramsController;
use App\Http\Controllers\Records\CourseController;
use App\Http\Controllers\Records\ProfessorController;
use App\Http\Controllers\Records\RoomController;
use App\Http\Controllers\Records\RoomExclusiveAcademicProgramsController;
use App\Http\Controllers\Records\RoomExclusiveDayController;
use App\Http\Controllers\Records\SpecializationController;
use App\Http\Controllers\Records\TimetableController;
use App\Http\Controllers\Timetabling\CourseSessionController;
use App\Http\Controllers\Timetabling\GenerateTimetableController;
use App\Http\Controllers\Timetabling\SessionGroupController;
use App\Http\Controllers\Timetabling\TimetableEditingPaneController;
use App\Http\Controllers\Timetabling\TimetableOverviewController;
use App\Http\Controllers\Timetabling\TimetableProfessorController;
use App\Http\Controllers\Timetabling\TimetableRoomController;
use App\Http\Controllers\Users\EmailVerificationController;
use App\Http\Controllers\Users\PasswordResetController;
use App\Http\Controllers\Users\ProfileController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Users\UserLogController;
use App\Http\Controllers\Users\WelcomeController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Default Page
Route::get('/', [WelcomeController::class, 'index'])->name('home');


// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [UserController::class, 'showRegisterForm'])->name('register.form');
    Route::post('/register', [UserController::class, 'register'])->name('register');

    Route::get('/login', [UserController::class, 'showLoginForm'])->name('login.form');
    Route::post('/login', [UserController::class, 'login'])->name('login');

    Route::get('/forgot-password', [PasswordResetController::class, 'create'])
        ->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'store'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])
        ->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])
        ->name('password.update');
});

// Authenticated Routes
Route::middleware([Authenticate::class])->group(function () {
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');

    // Top-level timetables resource
    Route::resource('timetables', TimetableController::class);
    Route::get(
        '/timetables/{timetable}/copy',
        [TimetableController::class, 'copy']
    )->name('timetables.copy');
    Route::post(
        '/timetables/{timetable}/store-copy',
        [TimetableController::class, 'storeCopy']
    )->name('timetables.store-copy');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // All routes that are nested under a specific timetable
    Route::prefix('timetables/{timetable}')
        ->name('timetables.')
        ->group(function () {
            // Timetable editing pane
            Route::resource('timetable-editing-pane', TimetableEditingPaneController::class)->only('index');
            Route::get('editor', [TimetableEditingPaneController::class, 'editor'])
                ->name('timetable-editing-pane.editor');
            Route::get('export-formatted', [TimetableEditingPaneController::class, 'exportFormattedSpreadsheet'])
                ->name('export_formatted');
            Route::post('editor/save', [TimetableEditingPaneController::class, 'saveFromEditor'])
                ->name('editor.save');

            // Session groups
            Route::resource('session-groups', SessionGroupController::class);
            Route::patch(
                'session-groups/{sessionGroup}/color',
                [SessionGroupController::class, 'updateColor']
            )->name('session-groups.update-color');
            // Copy session group (form + store)
            Route::get(
                'session-groups/{sessionGroup}/copy',
                [SessionGroupController::class, 'copy']
            )->name('session-groups.copy');
            Route::post(
                'session-groups/{sessionGroup}/copy',
                [SessionGroupController::class, 'storeCopy']
            )->name('session-groups.store-copy');
            // Bulk delete (view)
            Route::get(
                'session-groups/{sessionGroup}/course-sessions/delete',
                [CourseSessionController::class, 'delete']
            )->name('timetables.session-groups.course-sessions.delete');

            // Bulk delete (action)
            Route::post(
                'session-groups/{sessionGroup}/course-sessions/bulk-delete',
                [CourseSessionController::class, 'bulkDestroy']
            )->name('timetables.session-groups.course-sessions.bulk-destroy');

            Route::patch(
                'session-groups/{sessionGroup}/update-terms',
                [CourseSessionController::class, 'bulkUpdateTerms']
            )->name('session-groups.course-sessions.bulk-update-terms');
            // Course sessions inside a session group
            Route::resource(
                'session-groups.course-sessions',
                CourseSessionController::class
            )->only(['index','create', 'store', 'destroy']);
            Route::patch(
                'session-groups/{sessionGroup}/course-sessions/{courseSession}/update-term',
                [CourseSessionController::class, 'updateTerm']
            )->name('session-groups.course-sessions.update-term');
            // Bulk delete (selection screen)
            Route::get(
                'session-groups/{sessionGroup}/course-sessions/delete',
                [CourseSessionController::class, 'delete']
            )->name('session-groups.course-sessions.delete');
            // Bulk delete (action)
            Route::post(
                'session-groups/{sessionGroup}/course-sessions/bulk-delete',
                [CourseSessionController::class, 'bulkDestroy']
            )->name('session-groups.course-sessions.bulk-destroy');

            // Timetable professors
            Route::resource('timetable-professors', TimetableProfessorController::class)
                ->only('index', 'create', 'store');
            Route::delete(
                'timetable-professors/{professor}',
                [TimetableProfessorController::class, 'destroy']
            )->name('timetable-professors.destroy');

            // Timetable rooms
            Route::resource('timetable-rooms', TimetableRoomController::class)
                ->only('index', 'create', 'store');
            Route::delete(
                'timetable-rooms/{room}',
                [TimetableRoomController::class, 'destroy']
            )->name('timetable-rooms.destroy');

            // Generate timetable
            Route::resource('generate-timetable', GenerateTimetableController::class);
            Route::get('generate', [GenerateTimetableController::class, 'index'])
                ->name('generate');
            Route::post('generate', [GenerateTimetableController::class, 'generate'])
                ->name('generate.post');

            Route::get('overview', [TimetableOverviewController::class, 'index'])
                ->name('timetable-overview.index');
        });

    // Courses
    Route::resource('courses', CourseController::class);
    // Courses - Academic Programs (parallel to room-exclusive-academic-programs)
    Route::resource('courses.course-academic-programs', CourseAcademicProgramsController::class)
        ->only('index', 'create', 'store', 'destroy');

    // Professors
    Route::resource('professors', ProfessorController::class);
    Route::resource('professors.specializations', SpecializationController::class)
        ->only('index', 'create', 'store', 'destroy');

    // Academic Programs
    Route::resource('academic-programs', AcademicProgramController::class);

    // Rooms
    Route::resource('rooms', RoomController::class);
    Route::resource('rooms.room-exclusive-days', RoomExclusiveDayController::class)
        ->only('index', 'create', 'store', 'destroy');
    Route::resource('rooms.room-exclusive-academic-programs', RoomExclusiveAcademicProgramsController::class)
        ->only('index', 'create', 'store', 'destroy');

    //User Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Test route (Tinker)
    Route::get('/test-file', function() {
        $path = base_path("scripts/public/exports/timetables/1.xlsx");
        return file_exists($path) ? "Exists" : "Missing: $path";
    });

    // User Logs page
    Route::get('/admin/user-logs', [UserLogController::class, 'index'])
        ->name('admin.user-logs');

    Route::get('/debug-timetables', function () {
        return Storage::disk('facultime')->allFiles('timetables');
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
});
