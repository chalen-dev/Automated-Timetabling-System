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
                'password' => '$2y$12$MML.zRQRdk41mgQLzQaAo.MFWqj5vh/xgmvnob3KCJa1AixWDt7iK',
                'role' => 'admin',
            ]
        );
    }
}
