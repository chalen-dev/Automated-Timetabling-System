<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    //Instance variables
    private $courseTypeOptions = [
        'major' => 'Major',
        'minor' => 'Minor',
        'pe' => 'PE',
        'nstp' => 'NSTP',
        'others' => 'Others',
    ];

    private $durationTypeOptions = [
        'semestral' => 'Semestral',
        'term' => 'Term'
    ];

    //Instance Functions
    private function total_days_exceed_6($data){
        $lecture_days = $data->total_lecture_class_days;
        $lab_days = $data->total_laboratory_class_days;
        if (($lecture_days + $lab_days) > 6) {
            return true;
        }
        return false;
    }
    //Display all
    public function index(){
        $courses = Course::all();
        return view('records.courses.index', compact('courses'));
    }

    //Display specific course
    public function show(Course $course){
        return view('records.courses.show', compact('course'));
    }

    //Display create view
    public function create(){
        $courseTypeOptions = $this->courseTypeOptions;
        $durationTypeOptions = $this->durationTypeOptions;
        return view('records.courses.create', compact('courseTypeOptions', 'durationTypeOptions'));
    }

    //Store function for create view
    public function store(Request $request){

        if ($this->total_days_exceed_6($request)){
            return back()->withErrors([
                'total_days' => 'The total of lecture and laboratory days cannot exceed 6.'
            ])->withInput();
        }


        $validatedData = $request -> validate([
            'course_title' => 'required|string',
            'course_name' => 'required|string',
            'course_type' => 'required|string',
            'class_hours' => 'required|numeric|min:1|max:9',
            'total_lecture_class_days' => 'required|numeric|min:1|max:6',
            'total_laboratory_class_days' => 'required|numeric|min:1|max:6',
            'unit_load' => 'required|numeric|min:0.0|max:10.0',
            'duration_type' => 'required|string',
        ]);



        Course::create($validatedData);
        return redirect()->route('records.courses.index')
            ->with('success', 'Course created successfully.');
    }

    //Display edit view
    public function edit(Course $course){
        $courseTypeOptions = $this->courseTypeOptions;
        $durationTypeOptions = $this->durationTypeOptions;
        return view('records.courses.edit', compact('course', 'courseTypeOptions', 'durationTypeOptions'));
    }

    //Update course
    public function update(Request $request, Course $course){

        if ($this->total_days_exceed_6($request)){
            return back()->withErrors([
                'total_days' => 'The total of lecture and laboratory days cannot exceed 6.'
            ])->withInput();
        }

        $validatedData = $request -> validate([
            'course_title' => 'required|string',
            'course_name' => 'required|string',
            'course_type' => 'required|string',
            'class_hours' => 'required|numeric|min:1|max:9',
            'total_lecture_class_days' => 'required|numeric|min:1|max:6',
            'total_laboratory_class_days' => 'required|numeric|min:1|max:6',
            'unit_load' => 'required|numeric|min:0.0|max:10.0',
            'duration_type' => 'required|string',
        ]);

        $course->update($validatedData);

        return redirect()->route('records.courses.index')
            ->with('success', 'Course updated successfully');
    }

    public function destroy(Course $course){
        $course->delete();
        return redirect()->route('records.courses.index')
            ->with('success', 'Course deleted successfully');
    }




}


