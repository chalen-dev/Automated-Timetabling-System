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
                'name' => 'Criztyl',
                'email' => 'c.cabanero.143457.tc@umindanao.edu.ph',
                'password' => '$2y$12$ydgYRB4C9KILy4K.Emq9i.zScE6bbhMmREmWsMXbHFqRa6I.SBAvO',
                'role' => 'admin',
            ],
            [
                'name' => 'Choraezo',
                'email' => 'e.ramirez.143833.tc@umindanao.edu.ph',
                'password' => '$2y$12$U9Jg/4yYFFB7FzqTjg7jNufElvc.6ipcC2AIBtXeA5ZpS6mA.5LRi',
                'role' => 'admin',
            ],
            [
                'name' => 'Jeizerssss',
                'email' => 'j.gozon.143104.tc@umindanao.edu.ph',
                'password' => '$2y$12$XUyPPub0oy/Mr2hoBVhioef8B8lA5fUpX.CDCSjhexree0qmhjEYK',
                'role' => 'admin',
            ],
        ];
        User::insertOrIgnore($data);
    }
}
