<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Records\Timetable;
use App\Models\Users\User;

class TimetableSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        if (! $user) {
            // optionally create a demo user instead of returning
            // $user = User::factory()->create();
            return;
        }

        $data = [
            [
                'user_id' => $user->id,
                'timetable_name' => 'CSIT',
                'semester' => '1st',
                'academic_year' => '2025-2026',
                'timetable_description' => null,
            ],
            [
                'user_id' => $user->id,
                'timetable_name' => 'CSIT',
                'semester' => '2nd',
                'academic_year' => '2025-2026',
                'timetable_description' => null,
            ],
        ];

        Timetable::insertOrIgnore($data);
    }
}
