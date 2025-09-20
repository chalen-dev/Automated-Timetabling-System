<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{

    public function __construct()
    {
        //Literally apply middleware auth to all methods in this controller, cause you should
        $this->middleware('auth');
    }

    public function showDashboard(){

        //Get the authenticated user
        $user = auth()->user();

        //Display dashboard, while passing in the details of the authenticated user to the blade template/component
        return view('dashboard.index', compact('user'));
    }

}
