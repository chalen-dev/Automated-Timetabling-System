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
                'password' => '$2y$12$pKg/sd12505lr71CGOxf0OrHcq8InCBHdYccZTncIOS97GmDH25Dy',
                'role' => 'admin',
            ]
        );
    }
}
