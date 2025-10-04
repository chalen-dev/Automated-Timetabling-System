<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicProgram;
use App\Models\Course;
use App\Models\Professor;
use App\Models\Room;
use App\Models\SessionGroup;

class TableFillController extends Controller
{
    public function fill($table)
    {
        // Only allow these tables
        $allowedTables = ['academic_programs', 'courses', 'professors', 'rooms', 'session_groups'];
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
                        'course_title' => 'NSTP 1',
                        'course_name' => 'National Service Training Program 1',
                        'course_type' => 'nstp',
                        'class_hours' => 1,
                        'total_lecture_class_days' => 1,
                        'total_laboratory_class_days' => 0,
                        'unit_load' => 3,
                        'duration_type' => 'semestral'
                    ],
                ];
                Course::insertOrIgnore($data);
                break;

            case 'professors':
                //index 1 - CS
                //index 2 - IT
                //Only follows this order in sample data.
                $program = AcademicProgram::all(); // assign demo professors to first program
                $data = [
                    [
                        'first_name' => 'Lowell Jay',
                        'last_name' => 'Orcullo',
                        'professor_type' => 'Non-Regular',
                        'max_unit_load' => 18,
                        'professor_age' => 20,
                        'position' => 'Lecturer',
                        'academic_program_id' => $program[1]->id ?? null
                    ],
                    [
                        'first_name' => 'Kate',
                        'last_name' => 'Bruno',
                        'professor_type' => 'Non-Regular',
                        'max_unit_load' => 18,
                        'professor_age' => 20,
                        'position' => 'Lecturer',
                        'academic_program_id' => $program[1]->id ?? null
                    ],
                    [
                        'first_name' => 'Richard Vincent',
                        'last_name' => 'Misa',
                        'professor_type' => 'Regular',
                        'max_unit_load' => 24,
                        'professor_age' => 30,
                        'position' => 'Lecturer',
                        'academic_program_id' => $program[1]->id ?? null
                    ],
                    [
                        'first_name' => 'Iris',
                        'last_name' => 'iforgot sorry',
                        'professor_type' => 'Regular',
                        'max_unit_load' => 24,
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
                        'room_name' => 'CLV1',
                        'room_type' => 'comlab',
                        'course_type_exclusive_to' => 'none',
                        'room_capacity' => 50
                    ],
                    [
                        'room_name' => 'gym1',
                        'room_type' => 'gym',
                        'course_type_exclusive_to' => 'pe',
                        'room_capacity' => 50,
                    ],
                    [
                        'room_name' => 'main1',
                        'room_type' => 'main',
                        'course_type_exclusive_to' => 'nstp',
                        'room_capacity' => 50,
                    ],
                ];
                Room::insertOrIgnore($data);
                break;

        }

        return back()->with('success', ucfirst(str_replace('_',' ',$table)).' filled successfully!');
    }
}
