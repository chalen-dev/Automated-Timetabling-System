<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Users\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => '$2y$12$c2q5eXcx55XmpbL8eYfQhOB8qtCdO47fTzFd5BQEM20/0oUp/dHEu',
                'role' => 'admin',
            ]
        );
    }
}
