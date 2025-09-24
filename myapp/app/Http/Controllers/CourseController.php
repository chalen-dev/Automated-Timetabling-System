<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(){

        //Test Code Start
        $courses = [
            ['course_title' => 'IT 12', 'id' => 1],
            ['course_title' => 'IT 14', 'id' => 2],
        ];
        //Test code end

        return view('courses.index', compact('courses'));
    }

    public function show($id){
        return view('courses.show', ['id' => $id]);
    }
}
