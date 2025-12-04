<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Timetabling\TimetableRoom;

class TimetableRoomSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['timetable_id' => 1, 'room_id' => 1],
            ['timetable_id' => 1, 'room_id' => 2],
            ['timetable_id' => 1, 'room_id' => 3],
            ['timetable_id' => 1, 'room_id' => 4],
            ['timetable_id' => 1, 'room_id' => 5],
            ['timetable_id' => 1, 'room_id' => 6],
            ['timetable_id' => 1, 'room_id' => 7],
            ['timetable_id' => 1, 'room_id' => 8],
            ['timetable_id' => 1, 'room_id' => 9],
            ['timetable_id' => 1, 'room_id' => 10],
            ['timetable_id' => 1, 'room_id' => 11],
            ['timetable_id' => 1, 'room_id' => 12],
            ['timetable_id' => 1, 'room_id' => 13],
            ['timetable_id' => 1, 'room_id' => 14],
            ['timetable_id' => 1, 'room_id' => 15],
            ['timetable_id' => 1, 'room_id' => 16],
        ];

        TimetableRoom::insertOrIgnore($data);
    }
}
