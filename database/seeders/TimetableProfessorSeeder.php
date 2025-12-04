<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Timetabling\TimetableProfessor;

class TimetableProfessorSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['timetable_id' => 1, 'professor_id' => 1],
            ['timetable_id' => 1, 'professor_id' => 2],
            ['timetable_id' => 1, 'professor_id' => 3],
            ['timetable_id' => 1, 'professor_id' => 4],
            ['timetable_id' => 1, 'professor_id' => 5],
            ['timetable_id' => 1, 'professor_id' => 6],
        ];

        TimetableProfessor::insertOrIgnore($data);
    }
}
