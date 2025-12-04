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
                'password' => '$2y$12$GStIlJ9lRodi6mdib15oku3QrOBP1hu5WsJ6nd2v5R1VT3bqv.9PW',
                'role' => 'admin',
            ]
        );
    }
}
