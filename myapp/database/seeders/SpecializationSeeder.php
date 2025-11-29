<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Records\Professor;
use App\Models\Records\Course;
use App\Models\Records\Specialization;

class SpecializationSeeder extends Seeder
{
    public function run(): void
    {
        $professors = Professor::all();
        $courses = Course::where('course_type', 'major')->get();

        if ($professors->isEmpty() || $courses->isEmpty()) {
            return;
        }

        $data = [
            // Professor 1 -> 3 courses
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

            // Professor 2 -> 2 courses
            [
                'professor_id' => $professors[1]->id ?? null,
                'course_id' => $courses[3]->id ?? null,
            ],
            [
                'professor_id' => $professors[1]->id ?? null,
                'course_id' => $courses[4]->id ?? null,
            ],

            // Professor 3 -> 1 course
            [
                'professor_id' => $professors[2]->id ?? null,
                'course_id' => $courses[5]->id ?? null,
            ],

            // Professor 4 -> 2 courses
            [
                'professor_id' => $professors[3]->id ?? null,
                'course_id' => $courses[6]->id ?? null,
            ],
            [
                'professor_id' => $professors[3]->id ?? null,
                'course_id' => $courses[7]->id ?? null,
            ],

            // Professor 5 -> 2 courses
            [
                'professor_id' => $professors[4]->id ?? null,
                'course_id' => $courses[8]->id ?? null,
            ],
            [
                'professor_id' => $professors[4]->id ?? null,
                'course_id' => $courses[9]->id ?? null,
            ],

            // Professor 6 -> 2 courses
            [
                'professor_id' => $professors[5]->id ?? null,
                'course_id' => $courses[10]->id ?? null,
            ],
            [
                'professor_id' => $professors[5]->id ?? null,
                'course_id' => $courses[11]->id ?? null,
            ],
        ];

        // filter nulls
        $data = array_filter($data, function ($row) {
            return !is_null($row['professor_id']) && !is_null($row['course_id']);
        });

        Specialization::insertOrIgnore($data);
    }
}
