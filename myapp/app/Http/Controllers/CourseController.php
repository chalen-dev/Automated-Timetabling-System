<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(){

        $courses = Course::all();

        return view('Courses.index', compact('courses'));
    }

    public function show($id){
        $courses = Course::all();

        //Collect specific course details depending on the id
        $course = collect($courses)->firstWhere('id', $id);

        return view('Courses.show', compact('course'));
    }
}
