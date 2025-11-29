<?php

namespace Database\Seeders;

use App\Models\Users\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();


        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
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
