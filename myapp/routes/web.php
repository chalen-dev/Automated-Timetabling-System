<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->middleware('guest');

Route::get('/dashboard', function () {
    return view('pages.dashboard');
})->middleware('auth');

// Register
Route::get('/register', [AuthController::class, 'showRegisterForm'])->middleware('guest')->name('register.form');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest')->name('register');

// Login
Route::get('/login', [AuthController::class, 'showLoginForm'])->middleware('guest')->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->middleware('guest')->name('logout');
