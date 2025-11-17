<?php

namespace App\Http\Controllers\testing;

use App\Http\Controllers\Controller;
use App\Models\records\AcademicProgram;
use App\Models\records\Course;
use App\Models\records\Professor;
use App\Models\records\Room;
use App\Models\records\RoomExclusiveDay;
use App\Models\records\Timetable;
use App\Models\timetabling\CourseSession;
use App\Models\timetabling\SessionGroup;
use App\Models\timetabling\TimetableProfessor;
use App\Models\timetabling\TimetableRoom;

class TableFillController extends Controller
{
    public function fill($table)
    {
        //THIS CODE IS MEANT TO BE DIRTY FOLKS
        // convert URL-friendly names to actual table names
        $table = str_replace('-', '_', $table);

        // Only allow these tables
        $allowedTables = [
            'academic_programs',
            'courses',
            'professors',
            'rooms',
            'session_groups',
            'room_exclusive_days',
            'specializations',
            'timetables',
            'course_sessions',
            'timetable_professors',
            'timetable_rooms'
        ];
        if (!in_array($table, $allowedTables)) {
            abort(403, 'Table not allowed.');
        }

        switch ($table) {
            case 'academic_programs':
                $data = [
                    [
                        'program_name' => 'Bachelors of Science in Computer Science',
                        'program_abbreviation' => 'CS',
                        'program_description' => 'CS Program'
                    ],
                    [
                        'program_name' => 'Bachelors of Science in Information Technology',
                        'program_abbreviation' => 'IT',
                        'program_description' => 'IT Program'
                    ],
                ];
                AcademicProgram::insertOrIgnore($data);
                break;

            case 'courses':
                $data = [
                    [
                        'course_title' => 'CCE 101/L',
                        'course_name' => 'Introduction to Computing',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'GE 15',
                        'course_name' => 'Environmental Science',
                        'course_type' => 'minor',
                        'class_hours' => 1,
                        'total_lecture_class_days' => 3,
                        'total_laboratory_class_days' => 0,
                        'unit_load' => 3,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'GE 3',
                        'course_name' => 'The Contemporary World',
                        'course_type' => 'minor',
                        'class_hours' => 1,
                        'total_lecture_class_days' => 3,
                        'total_laboratory_class_days' => 0,
                        'unit_load' => 3,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'PAHF 1',
                        'course_name' => 'Movement Competency Training',
                        'course_type' => 'pe',
                        'class_hours' => 4,
                        'total_lecture_class_days' => 1,
                        'total_laboratory_class_days' => 0,
                        'unit_load' => 3,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'CCE 109/L',
                        'course_name' => 'Fundamentals of Programming',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'CS 8',
                        'course_name' => 'Social Issues and Professional Practice',
                        'course_type' => 'major',
                        'class_hours' => 1,
                        'total_lecture_class_days' => 3,
                        'total_laboratory_class_days' => 0,
                        'unit_load' => 3,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'GE 2',
                        'course_name' => 'Purposive Communication and Interactive Learning',
                        'course_type' => 'minor',
                        'class_hours' => 1,
                        'total_lecture_class_days' => 3,
                        'total_laboratory_class_days' => 0,
                        'unit_load' => 3,
                        'duration_type' => 'semestral'
                    ],
                    [
                        'course_title' => 'NSTP 1',
                        'course_name' => 'National Service Training Program 1',
                        'course_type' => 'nstp',
                        'class_hours' => 1,
                        'total_lecture_class_days' => 1,
                        'total_laboratory_class_days' => 0,
                        'unit_load' => 3,
                        'duration_type' => 'semestral'
                    ],
                    [
                        'course_title' => 'CCE 104/L',
                        'course_name' => 'Information Management',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'CS 26/L',
                        'course_name' => 'Software Development Fundamentals',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'PAHF 3',
                        'course_name' => 'Dance and Sports 1',
                        'course_type' => 'pe',
                        'class_hours' => 4,
                        'total_lecture_class_days' => 1,
                        'total_laboratory_class_days' => 0,
                        'unit_load' => 3,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'CS 3/L',
                        'course_name' => 'Discrete Structures 2',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'HCI 101',
                        'course_name' => 'Human Computer Interaction',
                        'course_type' => 'major',
                        'class_hours' => 1,
                        'total_lecture_class_days' => 3,
                        'total_laboratory_class_days' => 0,
                        'unit_load' => 3,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'MTH 103/L',
                        'course_name' => 'Probabilities and Statistics',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'MTH 105',
                        'course_name' => 'Integral Calculus',
                        'course_type' => 'major',
                        'class_hours' => 1,
                        'total_lecture_class_days' => 3,
                        'total_laboratory_class_days' => 0,
                        'unit_load' => 3,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'BSM 325',
                        'course_name' => 'Numerical Analysis',
                        'course_type' => 'major',
                        'class_hours' => 1,
                        'total_lecture_class_days' => 3,
                        'total_laboratory_class_days' => 0,
                        'unit_load' => 3,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'CS 12/L',
                        'course_name' => 'Software Engineering 1',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'CST 9/L',
                        'course_name' => 'CS Professional Track 3',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'GE 5',
                        'course_name' => 'Science, Technology, and Society',
                        'course_type' => 'minor',
                        'class_hours' => 1,
                        'total_lecture_class_days' => 3,
                        'total_laboratory_class_days' => 0,
                        'unit_load' => 3,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'CS 11/L',
                        'course_name' => 'Architecture and Organization',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'CS 15/L',
                        'course_name' => 'Programming Languages',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'GE 7',
                        'course_name' => 'Art Appreciation',
                        'course_type' => 'minor',
                        'class_hours' => 1,
                        'total_lecture_class_days' => 3,
                        'total_laboratory_class_days' => 0,
                        'unit_load' => 3,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'PHYS 101/L',
                        'course_name' => 'College Physics 1',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'CCE 106/L',
                        'course_name' => 'Application Development and Emerging Technologies',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'CS 18/L',
                        'course_name' => 'CS Thesis Writing 1',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'CS 19/L',
                        'course_name' => 'Operating Systems',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'CS 21/L',
                        'course_name' => 'Networks and Communications',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'CS 24/L',
                        'course_name' => 'CS Professional Track 6',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],

                    //IT
                    [
                        'course_title' => 'GE 4',
                        'course_name' => 'Mathematics in the Modern World',
                        'course_type' => 'major',
                        'class_hours' => 1,
                        'total_lecture_class_days' => 3,
                        'total_laboratory_class_days' => 0,
                        'unit_load' => 3,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'CCE 102/L',
                        'course_name' => 'Computer Programming 1',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'CCE 105/L',
                        'course_name' => 'Data Structures and Algorithms',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'IT 4',
                        'course_name' => 'Calculus 1',
                        'course_type' => 'major',
                        'class_hours' => 1,
                        'total_lecture_class_days' => 3,
                        'total_laboratory_class_days' => 0,
                        'unit_load' => 3,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'IT 3/L',
                        'course_name' => 'Networking 1',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'IT 5/L',
                        'course_name' => 'IT Elective 2',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'IT 11/L',
                        'course_name' => 'Networking 2',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'IT 14/L',
                        'course_name' => 'Professional Track for IT 5',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'GE 11',
                        'course_name' => 'The Entrepreneurial Mind',
                        'course_type' => 'minor',
                        'class_hours' => 1,
                        'total_lecture_class_days' => 3,
                        'total_laboratory_class_days' => 0,
                        'unit_load' => 3,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'IT 10/L',
                        'course_name' => 'IT Elective 3',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'IT 12/L',
                        'course_name' => 'Systems Integration and Architecture',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'IT 13/L',
                        'course_name' => 'Professional Track for IT 4',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'IT 23/L',
                        'course_name' => 'Systems Administration and Maintenance',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],
                    [
                        'course_title' => 'IT 24/L',
                        'course_name' => 'Capstone Project 2',
                        'course_type' => 'major',
                        'class_hours' => 2,
                        'total_lecture_class_days' => 2,
                        'total_laboratory_class_days' => 2,
                        'unit_load' => 4.5,
                        'duration_type' => 'term'
                    ],

                ];
                Course::insertOrIgnore($data);
                break;

            case 'professors':
                //index 0 - CS
                //index 1 - IT
                //Only follows this order in sample data.
                $program = AcademicProgram::all(); // assign demo professors to first program
                $data = [
                    [
                        'first_name' => 'Lowell Jay',
                        'last_name' => 'Orcullo',
                        'professor_type' => 'non-regular',
                        'max_unit_load' => 18,
                        'gender' => 'male',
                        'professor_age' => 20,
                        'position' => 'Lecturer',
                        'academic_program_id' => $program[1]->id ?? null
                    ],
                    [
                        'first_name' => 'Kate',
                        'last_name' => 'Bruno',
                        'professor_type' => 'non-regular',
                        'max_unit_load' => 18,
                        'gender' => 'female',
                        'professor_age' => 20,
                        'position' => 'Lecturer',
                        'academic_program_id' => $program[1]->id ?? null
                    ],
                    [
                        'first_name' => 'Eduardo',
                        'last_name' => 'Catahuran',
                        'professor_type' => 'non-regular',
                        'max_unit_load' => 18,
                        'gender' => 'male',
                        'professor_age' => 20,
                        'position' => 'Lecturer',
                        'academic_program_id' => $program[1]->id ?? null
                    ],
                    [
                        'first_name' => 'Richard Vincent',
                        'last_name' => 'Misa',
                        'professor_type' => 'regular',
                        'max_unit_load' => 24,
                        'gender' => 'male',
                        'professor_age' => 30,
                        'position' => 'Lecturer',
                        'academic_program_id' => $program[1]->id ?? null
                    ],
                    [
                        'first_name' => 'Sir',
                        'last_name' => 'Buddy',
                        'professor_type' => 'regular',
                        'max_unit_load' => 24,
                        'gender' => 'male',
                        'professor_age' => 30,
                        'position' => 'Program Head',
                        'academic_program_id' => $program[1]->id ?? null
                    ],
                    [
                        'first_name' => 'Ma\'am',
                        'last_name' => 'Iris',
                        'professor_type' => 'regular',
                        'max_unit_load' => 24,
                        'gender' => 'female',
                        'professor_age' => 30,
                        'position' => 'Program Head',
                        'academic_program_id' => $program[0]->id ?? null
                    ],
                ];
                Professor::insertOrIgnore($data);
                break;

            case 'rooms':
                $data = [
                    [
                        'room_name' => 'RM301',
                        'room_type' => 'lecture',
                        'course_type_exclusive_to' => 'none',
                        'room_capacity' => 50
                    ],
                    [
                        'room_name' => 'RM302',
                        'room_type' => 'lecture',
                        'course_type_exclusive_to' => 'none',
                        'room_capacity' => 50
                    ],
                    [
                        'room_name' => 'AVR',
                        'room_type' => 'lecture',
                        'course_type_exclusive_to' => 'none',
                        'room_capacity' => 50
                    ],
                    [
                        'room_name' => 'CLV1',
                        'room_type' => 'comlab',
                        'course_type_exclusive_to' => 'none',
                        'room_capacity' => 50
                    ],
                    [
                        'room_name' => 'CLV2',
                        'room_type' => 'comlab',
                        'course_type_exclusive_to' => 'none',
                        'room_capacity' => 50
                    ],
                    [
                        'room_name' => 'CLV3',
                        'room_type' => 'comlab',
                        'course_type_exclusive_to' => 'none',
                        'room_capacity' => 50
                    ],
                    [
                        'room_name' => 'gym1',
                        'room_type' => 'lecture',
                        'course_type_exclusive_to' => 'pe',
                        'room_capacity' => 50,
                    ],
                    [
                        'room_name' => 'gym2',
                        'room_type' => 'lecture',
                        'course_type_exclusive_to' => 'pe',
                        'room_capacity' => 50,
                    ],
                    [
                        'room_name' => 'gym3',
                        'room_type' => 'lecture',
                        'course_type_exclusive_to' => 'pe',
                        'room_capacity' => 50,
                    ],
                    [
                        'room_name' => 'gym4',
                        'room_type' => 'lecture',
                        'course_type_exclusive_to' => 'pe',
                        'room_capacity' => 50,
                    ],
                    [
                        'room_name' => 'gym5',
                        'room_type' => 'lecture',
                        'course_type_exclusive_to' => 'pe',
                        'room_capacity' => 50,
                    ],
                    [
                        'room_name' => 'main1',
                        'room_type' => 'lecture',
                        'course_type_exclusive_to' => 'nstp',
                        'room_capacity' => 50,
                    ],
                    [
                        'room_name' => 'main2',
                        'room_type' => 'lecture',
                        'course_type_exclusive_to' => 'nstp',
                        'room_capacity' => 50,
                    ],
                    [
                        'room_name' => 'main3',
                        'room_type' => 'lecture',
                        'course_type_exclusive_to' => 'nstp',
                        'room_capacity' => 50,
                    ],
                    [
                        'room_name' => 'main4',
                        'room_type' => 'lecture',
                        'course_type_exclusive_to' => 'nstp',
                        'room_capacity' => 50,
                    ],
                    [
                        'room_name' => 'main5',
                        'room_type' => 'lecture',
                        'course_type_exclusive_to' => 'nstp',
                        'room_capacity' => 50,
                    ],
                ];
                Room::insertOrIgnore($data);
                break;

            case 'room_exclusive_days':
                $data = [
                    [
                        'room_id' => 12,
                        'exclusive_day' => 'saturday',
                    ],
                    [
                        'room_id' => 13,
                        'exclusive_day' => 'saturday',
                    ],
                    [
                        'room_id' => 14,
                        'exclusive_day' => 'saturday',
                    ],
                    [
                        'room_id' => 15,
                        'exclusive_day' => 'saturday',
                    ],
                    [
                        'room_id' => 16,
                        'exclusive_day' => 'saturday',
                    ],
                ];
                RoomExclusiveDay::insertOrIgnore($data);
                break;

            case 'specializations':
                $professors = \App\Models\records\Professor::all();
                $courses = \App\Models\records\Course::where('course_type', 'major')->get();

                if ($professors->isEmpty() || $courses->isEmpty()) {
                    return back()->with('error', 'Please fill professors and courses tables first.');
                }

                $data = [
                    // Assign Professor 1 to 3 random major courses
                    [
                        'professor_id' => $professors[0]->id ?? null,
                        'course_id' => $courses[0]->id ?? null,
                    ],
                    [
                        'professor_id' => $professors[0]->id ?? null,
                        'course_id' => $courses[1]->id ?? null,
                    ],
                    [
                        'professor_id' => $professors[0]->id ?? null,
                        'course_id' => $courses[2]->id ?? null,
                    ],

                    // Assign Professor 2 to 2 random major courses
                    [
                        'professor_id' => $professors[1]->id ?? null,
                        'course_id' => $courses[3]->id ?? null,
                    ],
                    [
                        'professor_id' => $professors[1]->id ?? null,
                        'course_id' => $courses[4]->id ?? null,
                    ],

                    // Assign Professor 3 to 1 course
                    [
                        'professor_id' => $professors[2]->id ?? null,
                        'course_id' => $courses[5]->id ?? null,
                    ],

                    // Assign Professor 4 to 2 courses
                    [
                        'professor_id' => $professors[3]->id ?? null,
                        'course_id' => $courses[6]->id ?? null,
                    ],
                    [
                        'professor_id' => $professors[3]->id ?? null,
                        'course_id' => $courses[7]->id ?? null,
                    ],

                    // Assign Professor 5 (Program Head) to advanced CS subjects
                    [
                        'professor_id' => $professors[4]->id ?? null,
                        'course_id' => $courses[8]->id ?? null,
                    ],
                    [
                        'professor_id' => $professors[4]->id ?? null,
                        'course_id' => $courses[9]->id ?? null,
                    ],

                    // Assign Professor 6 (Program Head) to IT subjects
                    [
                        'professor_id' => $professors[5]->id ?? null,
                        'course_id' => $courses[10]->id ?? null,
                    ],
                    [
                        'professor_id' => $professors[5]->id ?? null,
                        'course_id' => $courses[11]->id ?? null,
                    ],
                ];

                // Clean out any nulls before insert
                $data = array_filter($data, function ($row) {
                    return !is_null($row['professor_id']) && !is_null($row['course_id']);
                });

                \App\Models\records\Specialization::insertOrIgnore($data);
                break;

            case 'timetables':
                $userId = auth()->user()->id;
                $data = [
                    [
                        'user_id' => $userId,
                        'timetable_name' => 'CSIT',
                        'semester' => '1st',
                        'academic_year' => '2025-2026',
                        'timetable_description' => null,
                    ],
                    [
                        'user_id' => $userId,
                        'timetable_name' => 'CSIT',
                        'semester' => '2nd',
                        'academic_year' => '2025-2026',
                        'timetable_description' => null,
                    ],
                ];
                Timetable::insertOrIgnore($data);
                break;

            case 'session_groups':
                //index 0 - CS
                //index 1 - IT
                //Only follows this order in sample data.
                $program = AcademicProgram::all(); // assign demo professors to first program
                $data = [
                    ['session_name' => 'A', 'year_level' => '1st', 'academic_program_id' => $program[0]->id, 'timetable_id' => 1],
                    ['session_name' => 'B', 'year_level' => '1st', 'academic_program_id' => $program[0]->id, 'timetable_id' => 1],
                    ['session_name' => 'A', 'year_level' => '2nd', 'academic_program_id' => $program[0]->id, 'timetable_id' => 1],
                    ['session_name' => 'B', 'year_level' => '2nd', 'academic_program_id' => $program[0]->id, 'timetable_id' => 1],
                    ['session_name' => 'A', 'year_level' => '3rd', 'academic_program_id' => $program[0]->id, 'timetable_id' => 1],
                    ['session_name' => 'B', 'year_level' => '3rd', 'academic_program_id' => $program[0]->id, 'timetable_id' => 1],
                    ['session_name' => 'A', 'year_level' => '4th', 'academic_program_id' => $program[0]->id, 'timetable_id' => 1],
                    ['session_name' => 'B', 'year_level' => '4th', 'academic_program_id' => $program[0]->id, 'timetable_id' => 1],
                    ['session_name' => 'A', 'year_level' => '1st', 'academic_program_id' => $program[1]->id, 'timetable_id' => 1],
                    ['session_name' => 'B', 'year_level' => '1st', 'academic_program_id' => $program[1]->id, 'timetable_id' => 1],
                    ['session_name' => 'A', 'year_level' => '2nd', 'academic_program_id' => $program[1]->id, 'timetable_id' => 1],
                    ['session_name' => 'B', 'year_level' => '2nd', 'academic_program_id' => $program[1]->id, 'timetable_id' => 1],
                    ['session_name' => 'A', 'year_level' => '3rd', 'academic_program_id' => $program[1]->id, 'timetable_id' => 1],
                    ['session_name' => 'B', 'year_level' => '3rd', 'academic_program_id' => $program[1]->id, 'timetable_id' => 1],
                    ['session_name' => 'A', 'year_level' => '4th', 'academic_program_id' => $program[1]->id, 'timetable_id' => 1],
                    ['session_name' => 'B', 'year_level' => '4th', 'academic_program_id' => $program[1]->id, 'timetable_id' => 1],
                ];


                SessionGroup::insertOrIgnore($data);
                break;

            /*
            case 'course_sessions':
                $data = [
                    //COMSCI SESSION GROUP IDs
                    //IDs for As- 1, 4, 7, 10
                    //IDs for Bs- 2, 5, 8, 11
                    //IDs for Cs -  3, 6, 9

                    //CS 1st Year
                    // A - id 1
                    [
                        'session_group_id' => 1,
                        'course_id' => 1,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 1,
                        'course_id' => 2,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 1,
                        'course_id' => 3,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 1,
                        'course_id' => 4,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 1,
                        'course_id' => 5,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 1,
                        'course_id' => 6,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 1,
                        'course_id' => 7,
                        'academic_term' => 'semestral',
                    ],
                    [
                        'session_group_id' => 1,
                        'course_id' => 8,
                        'academic_term' => 'semestral',
                    ],
                    //CS 2nd Yr
                    //A - id 4
                    [
                        'session_group_id' => 4,
                        'course_id' => 9,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 4,
                        'course_id' => 10,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 4,
                        'course_id' => 11,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 4,
                        'course_id' => 12,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 4,
                        'course_id' => 13,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 4,
                        'course_id' => 14,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 4,
                        'course_id' => 15,
                        'academic_term' => '2nd',
                    ],
                    //CS 3rd Year
                    //A - id 7
                    [
                        'session_group_id' => 7,
                        'course_id' => 16,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 7,
                        'course_id' => 17,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 7,
                        'course_id' => 18,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 7,
                        'course_id' => 19,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 7,
                        'course_id' => 20,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 7,
                        'course_id' => 21,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 7,
                        'course_id' => 22,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 7,
                        'course_id' => 23,
                        'academic_term' => '2nd',
                    ],
                    //CS 4th Year
                    //A - id 10
                    [
                        'session_group_id' => 10,
                        'course_id' => 24,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 10,
                        'course_id' => 25,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 10,
                        'course_id' => 26,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 10,
                        'course_id' => 27,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 10,
                        'course_id' => 28,
                        'academic_term' => '2nd',
                    ],

                    //IT SESSION GROUP IDs
                    //IDs for As- 12, 15, 18, 21
                    //IDs for Bs- 13, 16, 19, 22
                    //IDs for Cs - 14, 17, 20

                    //IT first year
                    //A - id 12
                    [
                        'session_group_id' => 12,
                        'course_id' => 1,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 12,
                        'course_id' => 3,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 12,
                        'course_id' => 29,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 12,
                        'course_id' => 4,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 12,
                        'course_id' => 30,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 12,
                        'course_id' => 2,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 12,
                        'course_id' => 7,
                        'academic_term' => 'semestral',
                    ],
                    [
                        'session_group_id' => 12,
                        'course_id' => 8,
                        'academic_term' => 'semestral',
                    ],

                    //IT second year
                    //A - id 15
                    [
                        'session_group_id' => 15,
                        'course_id' => 9,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 15,
                        'course_id' => 31,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 15,
                        'course_id' => 22,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 15,
                        'course_id' => 32,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 15,
                        'course_id' => 11,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 15,
                        'course_id' => 33,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 15,
                        'course_id' => 34,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 15,
                        'course_id' => 14,
                        'academic_term' => '2nd',
                    ],

                    //IT third year
                    //A - id 18
                    [
                        'session_group_id' => 18,
                        'course_id' => 24,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 18,
                        'course_id' => 35,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 18,
                        'course_id' => 36,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 18,
                        'course_id' => 37,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 18,
                        'course_id' => 38,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 18,
                        'course_id' => 39,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 18,
                        'course_id' => 40,
                        'academic_term' => '2nd',
                    ],

                    //IT fourth year
                    //A - id 21
                    [
                        'session_group_id' => 21,
                        'course_id' => 41,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 21,
                        'course_id' => 42,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => ,
                        'course_id' => ,
                        'academic_term' => '',
                    ],

                ];
                CourseSession::insertOrIgnore($data);
                break;
            */ //Original Course Sessions

            /*
            case 'course_sessions':
                $data = [
                    //COMSCI SESSION GROUP IDs
                    //IDs for As- 1, 4, 7, 10
                    //IDs for Bs- 2, 5, 8, 11
                    //IDs for Cs -  3, 6, 9

                    //CS 1st Year
                    // A - id 1
                    [
                        'session_group_id' => 1,
                        'course_id' => 1,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 1,
                        'course_id' => 2,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 1,
                        'course_id' => 3,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 1,
                        'course_id' => 4,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 1,
                        'course_id' => 5,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 1,
                        'course_id' => 6,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 1,
                        'course_id' => 7,
                        'academic_term' => 'semestral',
                    ],
                    [
                        'session_group_id' => 1,
                        'course_id' => 8,
                        'academic_term' => 'semestral',
                    ],

                    // B - id 2
                    [
                        'session_group_id' => 2,
                        'course_id' => 1,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 2,
                        'course_id' => 2,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 2,
                        'course_id' => 3,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 2,
                        'course_id' => 4,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 2,
                        'course_id' => 5,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 2,
                        'course_id' => 6,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 2,
                        'course_id' => 7,
                        'academic_term' => 'semestral',
                    ],
                    [
                        'session_group_id' => 2,
                        'course_id' => 8,
                        'academic_term' => 'semestral',
                    ],

                    // C - id 3
                    [
                        'session_group_id' => 3,
                        'course_id' => 1,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 3,
                        'course_id' => 2,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 3,
                        'course_id' => 3,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 3,
                        'course_id' => 4,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 3,
                        'course_id' => 5,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 3,
                        'course_id' => 6,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 3,
                        'course_id' => 7,
                        'academic_term' => 'semestral',
                    ],
                    [
                        'session_group_id' => 3,
                        'course_id' => 8,
                        'academic_term' => 'semestral',
                    ],
                    //CS 2nd Yr
                    //A - id 4
                    [
                        'session_group_id' => 4,
                        'course_id' => 9,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 4,
                        'course_id' => 10,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 4,
                        'course_id' => 11,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 4,
                        'course_id' => 12,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 4,
                        'course_id' => 13,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 4,
                        'course_id' => 14,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 4,
                        'course_id' => 15,
                        'academic_term' => '2nd',
                    ],
                    // B - id 5
                    [
                        'session_group_id' => 5,
                        'course_id' => 9,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 5,
                        'course_id' => 10,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 5,
                        'course_id' => 11,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 5,
                        'course_id' => 12,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 5,
                        'course_id' => 13,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 5,
                        'course_id' => 14,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 5,
                        'course_id' => 15,
                        'academic_term' => '2nd',
                    ],

                    // C - id 6
                    [
                        'session_group_id' => 6,
                        'course_id' => 9,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 6,
                        'course_id' => 10,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 6,
                        'course_id' => 11,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 6,
                        'course_id' => 12,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 6,
                        'course_id' => 13,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 6,
                        'course_id' => 14,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 6,
                        'course_id' => 15,
                        'academic_term' => '2nd',
                    ],
                    //CS 3rd Year
                    //A - id 7
                    [
                        'session_group_id' => 7,
                        'course_id' => 16,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 7,
                        'course_id' => 17,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 7,
                        'course_id' => 18,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 7,
                        'course_id' => 19,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 7,
                        'course_id' => 20,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 7,
                        'course_id' => 21,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 7,
                        'course_id' => 22,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 7,
                        'course_id' => 23,
                        'academic_term' => '2nd',
                    ],
                    // B - id 8
                    [
                        'session_group_id' => 8,
                        'course_id' => 16,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 8,
                        'course_id' => 17,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 8,
                        'course_id' => 18,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 8,
                        'course_id' => 19,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 8,
                        'course_id' => 20,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 8,
                        'course_id' => 21,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 8,
                        'course_id' => 22,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 8,
                        'course_id' => 23,
                        'academic_term' => '2nd',
                    ],

                    // C - id 9
                    [
                        'session_group_id' => 9,
                        'course_id' => 16,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 9,
                        'course_id' => 17,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 9,
                        'course_id' => 18,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 9,
                        'course_id' => 19,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 9,
                        'course_id' => 20,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 9,
                        'course_id' => 21,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 9,
                        'course_id' => 22,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 9,
                        'course_id' => 23,
                        'academic_term' => '2nd',
                    ],
                    //CS 4th Year
                    //A - id 10
                    [
                        'session_group_id' => 10,
                        'course_id' => 24,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 10,
                        'course_id' => 25,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 10,
                        'course_id' => 26,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 10,
                        'course_id' => 27,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 10,
                        'course_id' => 28,
                        'academic_term' => '2nd',
                    ],
                    // B - id 11
                    [
                        'session_group_id' => 11,
                        'course_id' => 24,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 11,
                        'course_id' => 25,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 11,
                        'course_id' => 26,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 11,
                        'course_id' => 27,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 11,
                        'course_id' => 28,
                        'academic_term' => '2nd',
                    ],

                    //IT SESSION GROUP IDs
                    //IDs for As- 12, 15, 18, 21
                    //IDs for Bs- 13, 16, 19, 22
                    //IDs for Cs - 14, 17, 20

                    //IT first year
                    //A - id 12
                    [
                        'session_group_id' => 12,
                        'course_id' => 1,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 12,
                        'course_id' => 3,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 12,
                        'course_id' => 29,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 12,
                        'course_id' => 4,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 12,
                        'course_id' => 30,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 12,
                        'course_id' => 2,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 12,
                        'course_id' => 7,
                        'academic_term' => 'semestral',
                    ],
                    [
                        'session_group_id' => 12,
                        'course_id' => 8,
                        'academic_term' => 'semestral',
                    ],

                    // B - id 13
                    [
                        'session_group_id' => 13,
                        'course_id' => 1,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 13,
                        'course_id' => 3,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 13,
                        'course_id' => 29,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 13,
                        'course_id' => 4,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 13,
                        'course_id' => 30,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 13,
                        'course_id' => 2,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 13,
                        'course_id' => 7,
                        'academic_term' => 'semestral',
                    ],
                    [
                        'session_group_id' => 13,
                        'course_id' => 8,
                        'academic_term' => 'semestral',
                    ],

                    // C - id 14
                    [
                        'session_group_id' => 14,
                        'course_id' => 1,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 14,
                        'course_id' => 3,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 14,
                        'course_id' => 29,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 14,
                        'course_id' => 4,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 14,
                        'course_id' => 30,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 14,
                        'course_id' => 2,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 14,
                        'course_id' => 7,
                        'academic_term' => 'semestral',
                    ],
                    [
                        'session_group_id' => 14,
                        'course_id' => 8,
                        'academic_term' => 'semestral',
                    ],

                    //IT second year
                    //A - id 15
                    [
                        'session_group_id' => 15,
                        'course_id' => 9,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 15,
                        'course_id' => 31,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 15,
                        'course_id' => 22,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 15,
                        'course_id' => 32,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 15,
                        'course_id' => 11,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 15,
                        'course_id' => 33,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 15,
                        'course_id' => 34,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 15,
                        'course_id' => 14,
                        'academic_term' => '2nd',
                    ],

                    // B - id 16
                    [
                        'session_group_id' => 16,
                        'course_id' => 9,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 16,
                        'course_id' => 31,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 16,
                        'course_id' => 22,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 16,
                        'course_id' => 32,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 16,
                        'course_id' => 11,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 16,
                        'course_id' => 33,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 16,
                        'course_id' => 34,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 16,
                        'course_id' => 14,
                        'academic_term' => '2nd',
                    ],

                    // C - id 17
                    [
                        'session_group_id' => 17,
                        'course_id' => 9,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 17,
                        'course_id' => 31,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 17,
                        'course_id' => 22,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 17,
                        'course_id' => 32,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 17,
                        'course_id' => 11,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 17,
                        'course_id' => 33,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 17,
                        'course_id' => 34,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 17,
                        'course_id' => 14,
                        'academic_term' => '2nd',
                    ],

                    //IT third year
                    //A - id 18
                    [
                        'session_group_id' => 18,
                        'course_id' => 24,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 18,
                        'course_id' => 35,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 18,
                        'course_id' => 36,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 18,
                        'course_id' => 37,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 18,
                        'course_id' => 38,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 18,
                        'course_id' => 39,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 18,
                        'course_id' => 40,
                        'academic_term' => '2nd',
                    ],

                    // B - id 19
                    [
                        'session_group_id' => 19,
                        'course_id' => 24,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 19,
                        'course_id' => 35,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 19,
                        'course_id' => 36,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 19,
                        'course_id' => 37,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 19,
                        'course_id' => 38,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 19,
                        'course_id' => 39,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 19,
                        'course_id' => 40,
                        'academic_term' => '2nd',
                    ],

                    // C - id 20
                    [
                        'session_group_id' => 20,
                        'course_id' => 24,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 20,
                        'course_id' => 35,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 20,
                        'course_id' => 36,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 20,
                        'course_id' => 37,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 20,
                        'course_id' => 38,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 20,
                        'course_id' => 39,
                        'academic_term' => '2nd',
                    ],
                    [
                        'session_group_id' => 20,
                        'course_id' => 40,
                        'academic_term' => '2nd',
                    ],

                    //IT fourth year
                    //A - id 21
                    [
                        'session_group_id' => 21,
                        'course_id' => 41,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 21,
                        'course_id' => 42,
                        'academic_term' => '2nd',
                    ],
                    // B - id 22
                    [
                        'session_group_id' => 22,
                        'course_id' => 41,
                        'academic_term' => '1st',
                    ],
                    [
                        'session_group_id' => 22,
                        'course_id' => 42,
                        'academic_term' => '2nd',
                    ],

                ];
                CourseSession::insertOrIgnore($data);
                break;
                */ //Version 2

            case 'course_sessions':
                $data = [
                    //COMSCI SESSION GROUPS
                    //CS 1st Year - A (new id 1)
                    ['session_group_id' => 1, 'course_id' => 1, 'academic_term' => '1st'],
                    ['session_group_id' => 1, 'course_id' => 2, 'academic_term' => '1st'],
                    ['session_group_id' => 1, 'course_id' => 3, 'academic_term' => '1st'],
                    ['session_group_id' => 1, 'course_id' => 4, 'academic_term' => '1st'],
                    ['session_group_id' => 1, 'course_id' => 5, 'academic_term' => '2nd'],
                    ['session_group_id' => 1, 'course_id' => 6, 'academic_term' => '2nd'],
                    ['session_group_id' => 1, 'course_id' => 7, 'academic_term' => 'semestral'],
                    ['session_group_id' => 1, 'course_id' => 8, 'academic_term' => 'semestral'],

                    //CS 1st Year - B (new id 2)
                    ['session_group_id' => 2, 'course_id' => 1, 'academic_term' => '1st'],
                    ['session_group_id' => 2, 'course_id' => 2, 'academic_term' => '1st'],
                    ['session_group_id' => 2, 'course_id' => 3, 'academic_term' => '1st'],
                    ['session_group_id' => 2, 'course_id' => 4, 'academic_term' => '1st'],
                    ['session_group_id' => 2, 'course_id' => 5, 'academic_term' => '2nd'],
                    ['session_group_id' => 2, 'course_id' => 6, 'academic_term' => '2nd'],
                    ['session_group_id' => 2, 'course_id' => 7, 'academic_term' => 'semestral'],
                    ['session_group_id' => 2, 'course_id' => 8, 'academic_term' => 'semestral'],

                    //CS 2nd Year - A (new id 3)
                    ['session_group_id' => 3, 'course_id' => 9, 'academic_term' => '1st'],
                    ['session_group_id' => 3, 'course_id' => 10, 'academic_term' => '1st'],
                    ['session_group_id' => 3, 'course_id' => 11, 'academic_term' => '1st'],
                    ['session_group_id' => 3, 'course_id' => 12, 'academic_term' => '2nd'],
                    ['session_group_id' => 3, 'course_id' => 13, 'academic_term' => '2nd'],
                    ['session_group_id' => 3, 'course_id' => 14, 'academic_term' => '2nd'],
                    ['session_group_id' => 3, 'course_id' => 15, 'academic_term' => '2nd'],

                    //CS 2nd Year - B (new id 4)
                    ['session_group_id' => 4, 'course_id' => 9, 'academic_term' => '1st'],
                    ['session_group_id' => 4, 'course_id' => 10, 'academic_term' => '1st'],
                    ['session_group_id' => 4, 'course_id' => 11, 'academic_term' => '1st'],
                    ['session_group_id' => 4, 'course_id' => 12, 'academic_term' => '2nd'],
                    ['session_group_id' => 4, 'course_id' => 13, 'academic_term' => '2nd'],
                    ['session_group_id' => 4, 'course_id' => 14, 'academic_term' => '2nd'],
                    ['session_group_id' => 4, 'course_id' => 15, 'academic_term' => '2nd'],

                    //CS 3rd Year - A (new id 5)
                    ['session_group_id' => 5, 'course_id' => 16, 'academic_term' => '1st'],
                    ['session_group_id' => 5, 'course_id' => 17, 'academic_term' => '1st'],
                    ['session_group_id' => 5, 'course_id' => 18, 'academic_term' => '1st'],
                    ['session_group_id' => 5, 'course_id' => 19, 'academic_term' => '1st'],
                    ['session_group_id' => 5, 'course_id' => 20, 'academic_term' => '2nd'],
                    ['session_group_id' => 5, 'course_id' => 21, 'academic_term' => '2nd'],
                    ['session_group_id' => 5, 'course_id' => 22, 'academic_term' => '2nd'],
                    ['session_group_id' => 5, 'course_id' => 23, 'academic_term' => '2nd'],

                    //CS 3rd Year - B (new id 6)
                    ['session_group_id' => 6, 'course_id' => 16, 'academic_term' => '1st'],
                    ['session_group_id' => 6, 'course_id' => 17, 'academic_term' => '1st'],
                    ['session_group_id' => 6, 'course_id' => 18, 'academic_term' => '1st'],
                    ['session_group_id' => 6, 'course_id' => 19, 'academic_term' => '1st'],
                    ['session_group_id' => 6, 'course_id' => 20, 'academic_term' => '2nd'],
                    ['session_group_id' => 6, 'course_id' => 21, 'academic_term' => '2nd'],
                    ['session_group_id' => 6, 'course_id' => 22, 'academic_term' => '2nd'],
                    ['session_group_id' => 6, 'course_id' => 23, 'academic_term' => '2nd'],

                    //CS 4th Year - A (new id 7)
                    ['session_group_id' => 7, 'course_id' => 24, 'academic_term' => '1st'],
                    ['session_group_id' => 7, 'course_id' => 25, 'academic_term' => '1st'],
                    ['session_group_id' => 7, 'course_id' => 26, 'academic_term' => '1st'],
                    ['session_group_id' => 7, 'course_id' => 27, 'academic_term' => '2nd'],
                    ['session_group_id' => 7, 'course_id' => 28, 'academic_term' => '2nd'],

                    //CS 4th Year - B (new id 8)
                    ['session_group_id' => 8, 'course_id' => 24, 'academic_term' => '1st'],
                    ['session_group_id' => 8, 'course_id' => 25, 'academic_term' => '1st'],
                    ['session_group_id' => 8, 'course_id' => 26, 'academic_term' => '1st'],
                    ['session_group_id' => 8, 'course_id' => 27, 'academic_term' => '2nd'],
                    ['session_group_id' => 8, 'course_id' => 28, 'academic_term' => '2nd'],

                    //IT first year - A (new id 9)
                    ['session_group_id' => 9, 'course_id' => 1, 'academic_term' => '1st'],
                    ['session_group_id' => 9, 'course_id' => 3, 'academic_term' => '1st'],
                    ['session_group_id' => 9, 'course_id' => 29, 'academic_term' => '1st'],
                    ['session_group_id' => 9, 'course_id' => 4, 'academic_term' => '1st'],
                    ['session_group_id' => 9, 'course_id' => 30, 'academic_term' => '2nd'],
                    ['session_group_id' => 9, 'course_id' => 2, 'academic_term' => '2nd'],
                    ['session_group_id' => 9, 'course_id' => 7, 'academic_term' => 'semestral'],
                    ['session_group_id' => 9, 'course_id' => 8, 'academic_term' => 'semestral'],

                    //IT first year - B (new id 10)
                    ['session_group_id' => 10, 'course_id' => 1, 'academic_term' => '1st'],
                    ['session_group_id' => 10, 'course_id' => 3, 'academic_term' => '1st'],
                    ['session_group_id' => 10, 'course_id' => 29, 'academic_term' => '1st'],
                    ['session_group_id' => 10, 'course_id' => 4, 'academic_term' => '1st'],
                    ['session_group_id' => 10, 'course_id' => 30, 'academic_term' => '2nd'],
                    ['session_group_id' => 10, 'course_id' => 2, 'academic_term' => '2nd'],
                    ['session_group_id' => 10, 'course_id' => 7, 'academic_term' => 'semestral'],
                    ['session_group_id' => 10, 'course_id' => 8, 'academic_term' => 'semestral'],

                    //IT second year - A (new id 11)
                    ['session_group_id' => 11, 'course_id' => 9, 'academic_term' => '1st'],
                    ['session_group_id' => 11, 'course_id' => 31, 'academic_term' => '1st'],
                    ['session_group_id' => 11, 'course_id' => 22, 'academic_term' => '1st'],
                    ['session_group_id' => 11, 'course_id' => 32, 'academic_term' => '1st'],
                    ['session_group_id' => 11, 'course_id' => 11, 'academic_term' => '1st'],
                    ['session_group_id' => 11, 'course_id' => 33, 'academic_term' => '2nd'],
                    ['session_group_id' => 11, 'course_id' => 34, 'academic_term' => '2nd'],
                    ['session_group_id' => 11, 'course_id' => 14, 'academic_term' => '2nd'],

                    //IT second year - B (new id 12)
                    ['session_group_id' => 12, 'course_id' => 9, 'academic_term' => '1st'],
                    ['session_group_id' => 12, 'course_id' => 31, 'academic_term' => '1st'],
                    ['session_group_id' => 12, 'course_id' => 22, 'academic_term' => '1st'],
                    ['session_group_id' => 12, 'course_id' => 32, 'academic_term' => '1st'],
                    ['session_group_id' => 12, 'course_id' => 11, 'academic_term' => '1st'],
                    ['session_group_id' => 12, 'course_id' => 33, 'academic_term' => '2nd'],
                    ['session_group_id' => 12, 'course_id' => 34, 'academic_term' => '2nd'],
                    ['session_group_id' => 12, 'course_id' => 14, 'academic_term' => '2nd'],

                    //IT third year - A (new id 13)
                    ['session_group_id' => 13, 'course_id' => 24, 'academic_term' => '1st'],
                    ['session_group_id' => 13, 'course_id' => 35, 'academic_term' => '1st'],
                    ['session_group_id' => 13, 'course_id' => 36, 'academic_term' => '1st'],
                    ['session_group_id' => 13, 'course_id' => 37, 'academic_term' => '2nd'],
                    ['session_group_id' => 13, 'course_id' => 38, 'academic_term' => '2nd'],
                    ['session_group_id' => 13, 'course_id' => 39, 'academic_term' => '2nd'],
                    ['session_group_id' => 13, 'course_id' => 40, 'academic_term' => '2nd'],

                    //IT third year - B (new id 14)
                    ['session_group_id' => 14, 'course_id' => 24, 'academic_term' => '1st'],
                    ['session_group_id' => 14, 'course_id' => 35, 'academic_term' => '1st'],
                    ['session_group_id' => 14, 'course_id' => 36, 'academic_term' => '1st'],
                    ['session_group_id' => 14, 'course_id' => 37, 'academic_term' => '2nd'],
                    ['session_group_id' => 14, 'course_id' => 38, 'academic_term' => '2nd'],
                    ['session_group_id' => 14, 'course_id' => 39, 'academic_term' => '2nd'],
                    ['session_group_id' => 14, 'course_id' => 40, 'academic_term' => '2nd'],

                    //IT fourth year - A (new id 15)
                    ['session_group_id' => 15, 'course_id' => 41, 'academic_term' => '1st'],
                    ['session_group_id' => 15, 'course_id' => 42, 'academic_term' => '2nd'],

                    //IT fourth year - B (new id 16)
                    ['session_group_id' => 16, 'course_id' => 41, 'academic_term' => '1st'],
                    ['session_group_id' => 16, 'course_id' => 42, 'academic_term' => '2nd'],

                ];
                CourseSession::insertOrIgnore($data);
                break;

            case 'timetable_professors':
                $data = [
                    [
                        'timetable_id' => 1,
                        'professor_id' => 1,
                    ],
                    [
                        'timetable_id' => 1,
                        'professor_id' => 2,
                    ],
                    [
                        'timetable_id' => 1,
                        'professor_id' => 3,
                    ],
                    [
                        'timetable_id' => 1,
                        'professor_id' => 4,
                    ],
                    [
                        'timetable_id' => 1,
                        'professor_id' => 5,
                    ],
                    [
                        'timetable_id' => 1,
                        'professor_id' => 6,
                    ],
                ];
                TimetableProfessor::insertOrIgnore($data);
                break;

            case 'timetable_rooms':
                $data = [
                    [
                        'timetable_id' => 1,
                        'room_id' => 1,
                    ],
                    [
                        'timetable_id' => 1,
                        'room_id' => 2,
                    ],
                    [
                        'timetable_id' => 1,
                        'room_id' => 3,
                    ],
                    [
                        'timetable_id' => 1,
                        'room_id' => 4,
                    ],
                    [
                        'timetable_id' => 1,
                        'room_id' => 5,
                    ],
                    [
                        'timetable_id' => 1,
                        'room_id' => 6,
                    ],
                    [
                        'timetable_id' => 1,
                        'room_id' => 7,
                    ],
                    [
                        'timetable_id' => 1,
                        'room_id' => 8,
                    ],
                    [
                        'timetable_id' => 1,
                        'room_id' => 9,
                    ],
                    [
                        'timetable_id' => 1,
                        'room_id' => 10,
                    ],
                    [
                        'timetable_id' => 1,
                        'room_id' => 11,
                    ],
                    [
                        'timetable_id' => 1,
                        'room_id' => 12,
                    ],
                    [
                        'timetable_id' => 1,
                        'room_id' => 13,
                    ],
                    [
                        'timetable_id' => 1,
                        'room_id' => 14,
                    ],
                    [
                        'timetable_id' => 1,
                        'room_id' => 15,
                    ],
                    [
                        'timetable_id' => 1,
                        'room_id' => 16,
                    ],
                ];

                TimetableRoom::insertOrIgnore($data);
                break;
        }


        $message = ucfirst(str_replace('_',' ',$table)) . ' filled successfully!';

        if (request()->isMethod('post')) {
            // Only redirect after a POST request (like submitting a form)
            return back()->with('success', $message);
        }

        // If GET, just stay on the same page (no redirect)
        session()->flash('success', $message);
        return redirect()->back();
    }
}
