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
        //Display dashboard, while passing in the details of the authenticated user to the blade template/component
        return view('dashboard.main-timetable-list');
    }

    public function showCourseList(){
        return view('dashboard.course-list');
    }

    public function showProfessorList(){
        return view('dashboard.professor-list');
    }

    public function showRoomList(){
        return view('dashboard.room-list');
    }

}
