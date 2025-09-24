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
        //Test Code Start
        $courses = [
            ['course_title' => 'IT 12', 'id' => 1],
            ['course_title' => 'IT 14', 'id' => 2],
        ];

        //Collect specific course details depending on the id
        $course = collect($courses)->firstWhere('id', $id);
        //Test code end

        return view('courses.show', compact('course'));
    }
}
