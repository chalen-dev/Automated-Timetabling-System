<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Timetabling\CourseSession;
use App\Models\Timetabling\SessionGroup;
use App\Models\Records\AcademicProgram;

class CourseSessionSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * Course offerings are defined PER PROGRAM + YEAR.
         * All session groups (A/B/C) under the same program + year
         * receive the same course sessions.
         */
        $offerings = [
            'CS' => [
                '1st' => [
                    [1, '1st'], [2, '1st'], [3, '1st'], [4, '1st'],
                    [5, '2nd'], [6, '2nd'],
                    [7, 'semestral'], [8, 'semestral'],
                ],
                '2nd' => [
                    [9, '1st'], [10, '1st'], [11, '1st'],
                    [12, '2nd'], [13, '2nd'], [14, '2nd'], [15, '2nd'],
                ],
                '3rd' => [
                    [16, '1st'], [17, '1st'], [18, '1st'], [19, '1st'],
                    [20, '2nd'], [21, '2nd'], [22, '2nd'], [23, '2nd'],
                ],
                '4th' => [
                    [24, '1st'], [25, '1st'], [26, '1st'],
                    [27, '2nd'], [28, '2nd'],
                ],
            ],

            'IT' => [
                '1st' => [
                    [1, '1st'], [3, '1st'], [29, '1st'], [4, '1st'],
                    [30, '2nd'], [2, '2nd'],
                    [7, 'semestral'], [8, 'semestral'],
                ],
                '2nd' => [
                    [9, '1st'], [31, '1st'], [22, '1st'], [32, '1st'], [11, '1st'],
                    [33, '2nd'], [34, '2nd'], [14, '2nd'],
                ],
                '3rd' => [
                    [24, '1st'], [35, '1st'], [36, '1st'],
                    [37, '2nd'], [38, '2nd'], [39, '2nd'], [40, '2nd'],
                ],
                '4th' => [
                    [41, '1st'],
                    [42, '2nd'],
                ],
            ],
        ];

        foreach ($offerings as $programCode => $years) {

            $program = AcademicProgram::where('program_abbreviation', $programCode)->firstOrFail();

            foreach ($years as $yearLevel => $courses) {

                $sessionGroups = SessionGroup::where('academic_program_id', $program->id)
                    ->where('year_level', $yearLevel)
                    ->get();

                foreach ($sessionGroups as $group) {
                    foreach ($courses as [$courseId, $term]) {
                        CourseSession::insertOrIgnore([
                            'session_group_id' => $group->id,
                            'course_id' => $courseId,
                            'academic_term' => $term,
                        ]);
                    }
                }
            }
        }
    }
}
