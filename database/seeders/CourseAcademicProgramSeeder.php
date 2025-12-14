<?php

namespace Database\Seeders;

use App\Models\Records\Course;
use App\Models\Records\CourseAcademicPrograms;
use Illuminate\Database\Seeder;

class CourseAcademicProgramSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * Map COURSE TITLE => program IDs
         *
         * - null or [] => OPEN to all programs (no pivot rows)
         * - [1,2,...]  => EXCLUSIVE allow-list (only those programs)
         *
         * academic_program_id reference:
         * 1 = CS, 2 = IT, 3 = CpE, 4 = EE, 5 = ECE
         */
        $map = [
            // --- CS set ---
            'CCE 101/L' => [1, 2],
            'GE 15' => null,
            'GE 3' => null,
            'PAHF 1' => null,
            'CCE 109/L' => [1, 2],
            'CS 8' => [1],
            'GE 2' => null,
            'NSTP 1' => null,
            'CCE 104/L' => [1, 2],
            'CS 26/L' => [1],
            'PAHF 3' => null,
            'CS 3/L' => [1],
            'HCI 101' => [1],
            'MTH 103/L' => [1, 2],
            'MTH 105' => [1],
            'BSM 325' => [1],
            'CS 12/L' => [1],
            'CST 9/L' => [1],
            'GE 5' => null,
            'CS 11/L' => [1],
            'CS 15/L' => [1],
            'GE 7' => null,
            'PHYS 101/L' => [1, 2],
            'CCE 106/L' => [1, 2],
            'CS 18/L' => [1],
            'CS 19/L' => [1],
            'CS 21/L' => [1],
            'CS 24/L' => [1],

            // --- IT set ---
            'GE 4' => null,
            'CCE 102/L' => [1, 2],
            'CCE 105/L' => [1, 2],
            'IT 4' => [2],
            'IT 3/L' => [2],
            'IT 5/L' => [2],
            'IT 11/L' => [2],
            'IT 14/L' => [2],
            'GE 11' => null,
            'IT 10/L' => [2],
            'IT 12/L' => [2],
            'IT 13/L' => [2],
            'IT 23/L' => [2],
            'IT 24/L' => [2],

            // Example restriction (edit as needed):
            // 'CCE 101/L' => [1, 2],
        ];

        $rowsToInsert = [];

        foreach ($map as $courseTitle => $programIds) {
            $course = Course::where('course_title', $courseTitle)->first();
            if (!$course) {
                continue;
            }

            // OPEN to all: ensure no pivot rows exist (so reruns stay consistent)
            if ($programIds === null || (is_array($programIds) && count($programIds) === 0)) {
                CourseAcademicPrograms::where('course_id', $course->id)->delete();
                continue;
            }

            // Restricted: reset and re-insert allow-list
            CourseAcademicPrograms::where('course_id', $course->id)->delete();

            foreach ((array) $programIds as $programId) {
                $rowsToInsert[] = [
                    'course_id' => $course->id,
                    'academic_program_id' => (int) $programId,
                ];
            }
        }

        CourseAcademicPrograms::insertOrIgnore($rowsToInsert);
    }
}
