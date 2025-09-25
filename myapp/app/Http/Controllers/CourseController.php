<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(){

        //Test Code Start
        $courses = [
            [
                'course_id' => 1,
                'course_title' => 'CCE 101/L',
                'course_name' => 'Introduction to Computing',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
            ],

            [
                'course_id' => 2,
                'course_title' => 'GE 2',
                'course_name' => 'Purposive Communication and Interactive Learning',
                'course_type' => 'minor',
                'class_hours' => 1,
                'total_lecture_class_days' => 3,
                'total_laboratory_class_days' => 0,
                'unit_load' => 3,
            ],

            [
                'course_id' => 3,
                'course_title' => 'PAHF 1',
                'course_name' => 'Movement Competency Training',
                'course_type' => 'pe',
                'class_hours' => 4,
                'total_lecture_class_days' => 1,
                'total_laboratory_class_days' => 0,
                'unit_load' => 2,
            ],

            [
                'course_id' => 4,
                'course_title' => 'NSTP 1',
                'course_name' => 'National Service Training Program 1',
                'course_type' => 'nstp',
                'class_hours' => 4,
                'total_lecture_class_days' => 1,
                'total_laboratory_class_days' => 0,
                'unit_load' => 2,
            ],
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
