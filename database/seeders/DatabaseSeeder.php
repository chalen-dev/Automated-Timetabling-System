<?php

namespace Database\Seeders;

use App\Models\Users\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $timetablePath = storage_path('app/exports/timetables');
        $inputCsvsPath = storage_path('app/exports/input-csvs');

        // Delete all files inside the folder, but keep the folder
        if (File::exists($timetablePath)) {
            File::cleanDirectory($timetablePath);
        }

        if (File::exists($inputCsvsPath)) {
            File::cleanDirectory($inputCsvsPath);
        }

        // Delete all files in the "timetables" folder on the facultime disk (bucket)
        // Locally this just cleans storage/app/exports/timetables as well,
        // on Laravel Cloud it clears the bucket prefix "timetables/"
        if (Storage::disk('facultime')->exists('timetables')) {
            Storage::disk('facultime')->deleteDirectory('timetables');
        }

        /*
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        */

        $this->call([
            AdminUserSeeder::class,
            //UserSeeder::class,
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
