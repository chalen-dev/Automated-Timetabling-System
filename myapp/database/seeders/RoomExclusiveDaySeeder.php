<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Records\RoomExclusiveDay;

class RoomExclusiveDaySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'room_id' => 12,
                'exclusive_day' => 'saturday',
            ],
            [
                'room_id' => 13,
                'exclusive_day' => 'saturday',
            ],
            [
                'room_id' => 14,
                'exclusive_day' => 'saturday',
            ],
            [
                'room_id' => 15,
                'exclusive_day' => 'saturday',
            ],
            [
                'room_id' => 16,
                'exclusive_day' => 'saturday',
            ],
        ];

        RoomExclusiveDay::insertOrIgnore($data);
    }
}
