<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/login', function () {
    return view('auth.login'); // resources/views/auth/login.blade.php
});

Route::get('/register', function () {
    return view('auth.register'); // resources/views/auth/register.blade.php
});
