<?php

namespace Database\Seeders;

use App\Models\Records\AcademicProgram;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AcademicProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
    }
}
