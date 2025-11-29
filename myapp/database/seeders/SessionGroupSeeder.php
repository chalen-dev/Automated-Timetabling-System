<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Records\AcademicProgram;
use App\Models\Timetabling\SessionGroup;

class SessionGroupSeeder extends Seeder
{
    public function run(): void
    {
        $programs = AcademicProgram::all();

        $colors = [
            "#ffe0e0",
            "#ffc4c4",
            "#ffe8cc",
            "#ffdcb3",
            "#fff6cc",
            "#fff2b3",
            "#e0ffe0",
            "#ccf5d5",
            "#e0fff7",
            "#ccf5f0",
            "#e0eaff",
            "#ccdfff",
            "#e3e0ff",
            "#d5d0ff",
            "#f0e0ff",
            "#ebd0ff",
        ];

        $data = [];
        $colorIndex = 0;

        $groups = [
            // CS
            ['A', '1st', $programs[0]->id],
            ['B', '1st', $programs[0]->id],
            ['A', '2nd', $programs[0]->id],
            ['B', '2nd', $programs[0]->id],
            ['A', '3rd', $programs[0]->id],
            ['B', '3rd', $programs[0]->id],
            ['A', '4th', $programs[0]->id],
            ['B', '4th', $programs[0]->id],

            // IT
            ['A', '1st', $programs[1]->id],
            ['B', '1st', $programs[1]->id],
            ['A', '2nd', $programs[1]->id],
            ['B', '2nd', $programs[1]->id],
            ['A', '3rd', $programs[1]->id],
            ['B', '3rd', $programs[1]->id],
            ['A', '4th', $programs[1]->id],
            ['B', '4th', $programs[1]->id],
        ];

        foreach ($groups as $g) {
            $data[] = [
                'session_name' => $g[0],
                'year_level' => $g[1],
                'academic_program_id' => $g[2],
                'timetable_id' => 1,
                'session_color' => $colors[$colorIndex++],
            ];
        }

        SessionGroup::insertOrIgnore($data);
    }

}
