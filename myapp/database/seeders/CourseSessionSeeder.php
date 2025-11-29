<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Timetabling\CourseSession;

class CourseSessionSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // CS 1st Year - A (id 1)
            ['session_group_id' => 1, 'course_id' => 1, 'academic_term' => '1st'],
            ['session_group_id' => 1, 'course_id' => 2, 'academic_term' => '1st'],
            ['session_group_id' => 1, 'course_id' => 3, 'academic_term' => '1st'],
            ['session_group_id' => 1, 'course_id' => 4, 'academic_term' => '1st'],
            ['session_group_id' => 1, 'course_id' => 5, 'academic_term' => '2nd'],
            ['session_group_id' => 1, 'course_id' => 6, 'academic_term' => '2nd'],
            ['session_group_id' => 1, 'course_id' => 7, 'academic_term' => 'semestral'],
            ['session_group_id' => 1, 'course_id' => 8, 'academic_term' => 'semestral'],

            // CS 1st Year - B (id 2)
            ['session_group_id' => 2, 'course_id' => 1, 'academic_term' => '1st'],
            ['session_group_id' => 2, 'course_id' => 2, 'academic_term' => '1st'],
            ['session_group_id' => 2, 'course_id' => 3, 'academic_term' => '1st'],
            ['session_group_id' => 2, 'course_id' => 4, 'academic_term' => '1st'],
            ['session_group_id' => 2, 'course_id' => 5, 'academic_term' => '2nd'],
            ['session_group_id' => 2, 'course_id' => 6, 'academic_term' => '2nd'],
            ['session_group_id' => 2, 'course_id' => 7, 'academic_term' => 'semestral'],
            ['session_group_id' => 2, 'course_id' => 8, 'academic_term' => 'semestral'],

            // CS 2nd Year - A (id 3)
            ['session_group_id' => 3, 'course_id' => 9, 'academic_term' => '1st'],
            ['session_group_id' => 3, 'course_id' => 10, 'academic_term' => '1st'],
            ['session_group_id' => 3, 'course_id' => 11, 'academic_term' => '1st'],
            ['session_group_id' => 3, 'course_id' => 12, 'academic_term' => '2nd'],
            ['session_group_id' => 3, 'course_id' => 13, 'academic_term' => '2nd'],
            ['session_group_id' => 3, 'course_id' => 14, 'academic_term' => '2nd'],
            ['session_group_id' => 3, 'course_id' => 15, 'academic_term' => '2nd'],

            // CS 2nd Year - B (id 4)
            ['session_group_id' => 4, 'course_id' => 9, 'academic_term' => '1st'],
            ['session_group_id' => 4, 'course_id' => 10, 'academic_term' => '1st'],
            ['session_group_id' => 4, 'course_id' => 11, 'academic_term' => '1st'],
            ['session_group_id' => 4, 'course_id' => 12, 'academic_term' => '2nd'],
            ['session_group_id' => 4, 'course_id' => 13, 'academic_term' => '2nd'],
            ['session_group_id' => 4, 'course_id' => 14, 'academic_term' => '2nd'],
            ['session_group_id' => 4, 'course_id' => 15, 'academic_term' => '2nd'],

            // CS 3rd Year - A (id 5)
            ['session_group_id' => 5, 'course_id' => 16, 'academic_term' => '1st'],
            ['session_group_id' => 5, 'course_id' => 17, 'academic_term' => '1st'],
            ['session_group_id' => 5, 'course_id' => 18, 'academic_term' => '1st'],
            ['session_group_id' => 5, 'course_id' => 19, 'academic_term' => '1st'],
            ['session_group_id' => 5, 'course_id' => 20, 'academic_term' => '2nd'],
            ['session_group_id' => 5, 'course_id' => 21, 'academic_term' => '2nd'],
            ['session_group_id' => 5, 'course_id' => 22, 'academic_term' => '2nd'],
            ['session_group_id' => 5, 'course_id' => 23, 'academic_term' => '2nd'],

            // CS 3rd Year - B (id 6)
            ['session_group_id' => 6, 'course_id' => 16, 'academic_term' => '1st'],
            ['session_group_id' => 6, 'course_id' => 17, 'academic_term' => '1st'],
            ['session_group_id' => 6, 'course_id' => 18, 'academic_term' => '1st'],
            ['session_group_id' => 6, 'course_id' => 19, 'academic_term' => '1st'],
            ['session_group_id' => 6, 'course_id' => 20, 'academic_term' => '2nd'],
            ['session_group_id' => 6, 'course_id' => 21, 'academic_term' => '2nd'],
            ['session_group_id' => 6, 'course_id' => 22, 'academic_term' => '2nd'],
            ['session_group_id' => 6, 'course_id' => 23, 'academic_term' => '2nd'],

            // CS 4th Year - A (id 7)
            ['session_group_id' => 7, 'course_id' => 24, 'academic_term' => '1st'],
            ['session_group_id' => 7, 'course_id' => 25, 'academic_term' => '1st'],
            ['session_group_id' => 7, 'course_id' => 26, 'academic_term' => '1st'],
            ['session_group_id' => 7, 'course_id' => 27, 'academic_term' => '2nd'],
            ['session_group_id' => 7, 'course_id' => 28, 'academic_term' => '2nd'],

            // CS 4th Year - B (id 8)
            ['session_group_id' => 8, 'course_id' => 24, 'academic_term' => '1st'],
            ['session_group_id' => 8, 'course_id' => 25, 'academic_term' => '1st'],
            ['session_group_id' => 8, 'course_id' => 26, 'academic_term' => '1st'],
            ['session_group_id' => 8, 'course_id' => 27, 'academic_term' => '2nd'],
            ['session_group_id' => 8, 'course_id' => 28, 'academic_term' => '2nd'],

            // IT 1st Year - A (id 9)
            ['session_group_id' => 9, 'course_id' => 1, 'academic_term' => '1st'],
            ['session_group_id' => 9, 'course_id' => 3, 'academic_term' => '1st'],
            ['session_group_id' => 9, 'course_id' => 29, 'academic_term' => '1st'],
            ['session_group_id' => 9, 'course_id' => 4, 'academic_term' => '1st'],
            ['session_group_id' => 9, 'course_id' => 30, 'academic_term' => '2nd'],
            ['session_group_id' => 9, 'course_id' => 2, 'academic_term' => '2nd'],
            ['session_group_id' => 9, 'course_id' => 7, 'academic_term' => 'semestral'],
            ['session_group_id' => 9, 'course_id' => 8, 'academic_term' => 'semestral'],

            // IT 1st Year - B (id 10)
            ['session_group_id' => 10, 'course_id' => 1, 'academic_term' => '1st'],
            ['session_group_id' => 10, 'course_id' => 3, 'academic_term' => '1st'],
            ['session_group_id' => 10, 'course_id' => 29, 'academic_term' => '1st'],
            ['session_group_id' => 10, 'course_id' => 4, 'academic_term' => '1st'],
            ['session_group_id' => 10, 'course_id' => 30, 'academic_term' => '2nd'],
            ['session_group_id' => 10, 'course_id' => 2, 'academic_term' => '2nd'],
            ['session_group_id' => 10, 'course_id' => 7, 'academic_term' => 'semestral'],
            ['session_group_id' => 10, 'course_id' => 8, 'academic_term' => 'semestral'],

            // IT 2nd Year - A (id 11)
            ['session_group_id' => 11, 'course_id' => 9, 'academic_term' => '1st'],
            ['session_group_id' => 11, 'course_id' => 31, 'academic_term' => '1st'],
            ['session_group_id' => 11, 'course_id' => 22, 'academic_term' => '1st'],
            ['session_group_id' => 11, 'course_id' => 32, 'academic_term' => '1st'],
            ['session_group_id' => 11, 'course_id' => 11, 'academic_term' => '1st'],
            ['session_group_id' => 11, 'course_id' => 33, 'academic_term' => '2nd'],
            ['session_group_id' => 11, 'course_id' => 34, 'academic_term' => '2nd'],
            ['session_group_id' => 11, 'course_id' => 14, 'academic_term' => '2nd'],

            // IT 2nd Year - B (id 12)
            ['session_group_id' => 12, 'course_id' => 9, 'academic_term' => '1st'],
            ['session_group_id' => 12, 'course_id' => 31, 'academic_term' => '1st'],
            ['session_group_id' => 12, 'course_id' => 22, 'academic_term' => '1st'],
            ['session_group_id' => 12, 'course_id' => 32, 'academic_term' => '1st'],
            ['session_group_id' => 12, 'course_id' => 11, 'academic_term' => '1st'],
            ['session_group_id' => 12, 'course_id' => 33, 'academic_term' => '2nd'],
            ['session_group_id' => 12, 'course_id' => 34, 'academic_term' => '2nd'],
            ['session_group_id' => 12, 'course_id' => 14, 'academic_term' => '2nd'],

            // IT 3rd Year - A (id 13)
            ['session_group_id' => 13, 'course_id' => 24, 'academic_term' => '1st'],
            ['session_group_id' => 13, 'course_id' => 35, 'academic_term' => '1st'],
            ['session_group_id' => 13, 'course_id' => 36, 'academic_term' => '1st'],
            ['session_group_id' => 13, 'course_id' => 37, 'academic_term' => '2nd'],
            ['session_group_id' => 13, 'course_id' => 38, 'academic_term' => '2nd'],
            ['session_group_id' => 13, 'course_id' => 39, 'academic_term' => '2nd'],
            ['session_group_id' => 13, 'course_id' => 40, 'academic_term' => '2nd'],

            // IT 3rd Year - B (id 14)
            ['session_group_id' => 14, 'course_id' => 24, 'academic_term' => '1st'],
            ['session_group_id' => 14, 'course_id' => 35, 'academic_term' => '1st'],
            ['session_group_id' => 14, 'course_id' => 36, 'academic_term' => '1st'],
            ['session_group_id' => 14, 'course_id' => 37, 'academic_term' => '2nd'],
            ['session_group_id' => 14, 'course_id' => 38, 'academic_term' => '2nd'],
            ['session_group_id' => 14, 'course_id' => 39, 'academic_term' => '2nd'],
            ['session_group_id' => 14, 'course_id' => 40, 'academic_term' => '2nd'],

            // IT 4th Year - A (id 15)
            ['session_group_id' => 15, 'course_id' => 41, 'academic_term' => '1st'],
            ['session_group_id' => 15, 'course_id' => 42, 'academic_term' => '2nd'],

            // IT 4th Year - B (id 16)
            ['session_group_id' => 16, 'course_id' => 41, 'academic_term' => '1st'],
            ['session_group_id' => 16, 'course_id' => 42, 'academic_term' => '2nd'],
        ];

        CourseSession::insertOrIgnore($data);
    }
}
