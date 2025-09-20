<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;


//Homepage or Dashboard, depending on authentication status
Route::get('/', [HomeController::class, 'index']);

// Register
Route::get('/register', [AuthController::class, 'showRegisterForm'])->middleware('guest')->name('register.form');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest')->name('register');

// Login
Route::get('/login', [AuthController::class, 'showLoginForm'])->middleware('guest')->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
