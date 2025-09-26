<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    //Display all
    public function index(){
        $courses = Course::all();
        return view('courses.index', compact('courses'));
    }

    //Display specific course
    public function show(Course $course){
        return view('courses.show', compact('course'));
    }

    //Display create view
    public function create(){
        return view('courses.create');
    }

    //Store function for create view
    public function store(Request $request){

        if (isset($request['course_type']) && is_array($request['course_type'])) {
            $request['course_type'] = reset($request['course_type']); // take the first value
        }

        $validatedData = $request -> validate([
                'course_title' => 'required|string',
                'course_name' => 'required|string',
                'course_type' => 'required|string',
                'class_hours' => 'required|numeric',
                'total_lecture_class_days' => 'required|numeric',
                'total_laboratory_class_days' => 'required|numeric',
                'unit_load' => 'required|numeric',
                'duration_type' => 'required|string',
        ]);

        Course::create($validatedData);
        return redirect()->route('courses.index')
            ->with('success', 'Course created successfully.');
    }

    //Display edit view
    public function edit(Course $course){
        return view('Courses.edit', compact('course'));
    }

    //Update course
    public function update(Request $request, Course $course){
        $request -> validate([
            'course_title' => 'required',
            'course_name' => 'required',
            'course_type' => 'required',
            'class_hours' => 'required',
            'total_lecture_class_days' => 'required',
            'total_laboratory_class_days' => 'required',
            'unit_load' => 'required',
            'duration_type' => 'required',
        ]);

        $course->update($request->all());

        return redirect()->route('courses.index')
            ->with('success', 'Course updated successfully');
    }

    public function destroy(Course $course){
        $course->delete();
        return redirect()->route('courses.index')
            ->with('success', 'Course deleted successfully');
    }



}


