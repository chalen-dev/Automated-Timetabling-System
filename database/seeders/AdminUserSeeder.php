<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Users\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => '$2a$12$X/mWzEzcXHX2LxztYRlxweujBXbWgm2zjt0q3gBs2gRo1T2ZsuVC6',
                'role' => 'admin',
            ]
        );
    }
}
