<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index(){
        if (auth()->check())
            return view('pages.dashboard');

        return view('welcome');
    }
}
