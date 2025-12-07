<?php

namespace Database\Seeders;

use App\Models\Users\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'XXX',
                'email' => 'XXX@example.com',
                'password' => 'XXX',
                'role' => 'admin',
            ]
        ];
        User::insertOrIgnore($data);
    }
}
