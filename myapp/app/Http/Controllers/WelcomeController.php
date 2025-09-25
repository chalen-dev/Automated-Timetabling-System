<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index(){
        //If not authenticated, redirect to login
        if (!auth()->check())
            return view('welcome');

        //Else, redirect to Dashboard and pass in user details.
        $user = auth()->user();
        return view('Dashboard.index', compact('user'));


    }
}
