<?php

namespace Database\Seeders;

use App\Models\Users\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $timetablePath = storage_path('app/exports/timetables');

        // Delete all files inside the folder, but keep the folder
        if (File::exists($timetablePath)) {
            File::cleanDirectory($timetablePath);
        }

        // User::factory(10)->create();


        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            AdminUserSeeder::class,
            AcademicProgramSeeder::class,
            CourseSeeder::class,
            ProfessorSeeder::class,
            RoomSeeder::class,
            RoomExclusiveDaySeeder::class,
            SpecializationSeeder::class,
            TimetableSeeder::class,
            SessionGroupSeeder::class,
            CourseSessionSeeder::class,
            TimetableProfessorSeeder::class,
            TimetableRoomSeeder::class,
        ]);
    }
}
