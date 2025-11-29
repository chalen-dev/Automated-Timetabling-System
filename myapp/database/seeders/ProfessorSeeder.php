<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Records\Professor;
use App\Models\Records\AcademicProgram;

class ProfessorSeeder extends Seeder
{
    public function run(): void
    {
        $programs = AcademicProgram::all();

        if ($programs->count() < 2) {
            // not enough programs, skip
            return;
        }

        $data = [
            [
                'first_name' => 'Lowell Jay',
                'last_name' => 'Orcullo',
                'professor_type' => 'non-regular',
                'max_unit_load' => 18,
                'gender' => 'male',
                'professor_age' => 20,
                'position' => 'Lecturer',
                'academic_program_id' => $programs[1]->id ?? null
            ],
            [
                'first_name' => 'Kate',
                'last_name' => 'Bruno',
                'professor_type' => 'non-regular',
                'max_unit_load' => 18,
                'gender' => 'female',
                'professor_age' => 20,
                'position' => 'Lecturer',
                'academic_program_id' => $programs[1]->id ?? null
            ],
            [
                'first_name' => 'Eduardo',
                'last_name' => 'Catahuran',
                'professor_type' => 'non-regular',
                'max_unit_load' => 18,
                'gender' => 'male',
                'professor_age' => 20,
                'position' => 'Lecturer',
                'academic_program_id' => $programs[1]->id ?? null
            ],
            [
                'first_name' => 'Richard Vincent',
                'last_name' => 'Misa',
                'professor_type' => 'regular',
                'max_unit_load' => 24,
                'gender' => 'male',
                'professor_age' => 30,
                'position' => 'Lecturer',
                'academic_program_id' => $programs[1]->id ?? null
            ],
            [
                'first_name' => 'Sir',
                'last_name' => 'Buddy',
                'professor_type' => 'regular',
                'max_unit_load' => 24,
                'gender' => 'male',
                'professor_age' => 30,
                'position' => 'Program Head',
                'academic_program_id' => $programs[1]->id ?? null
            ],
            [
                'first_name' => 'Ma\'am',
                'last_name' => 'Iris',
                'professor_type' => 'regular',
                'max_unit_load' => 24,
                'gender' => 'female',
                'professor_age' => 30,
                'position' => 'Program Head',
                'academic_program_id' => $programs[0]->id ?? null
            ],
        ];

        Professor::insertOrIgnore($data);
    }
}
