<?php

namespace Database\Seeders;

use App\Models\Users\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'test1',
                'email' => 'test1',
                'password' => Hash::make('AAAAAAAA'),
                'academic_program_id' => 1,
                'role' => 'user',
            ],
            [
                'name' => 'test2',
                'email' => 'test2',
                'password' => Hash::make('AAAAAAAA'),
                'academic_program_id' => 1,
                'role' => 'user',
            ],
            [
                'name' => 'test3',
                'email' => 'test3',
                'password' => Hash::make('AAAAAAAA'),
                'academic_program_id' => 3,
                'role' => 'user',
            ],
        ];
        User::insertOrIgnore($data);
    }
}
