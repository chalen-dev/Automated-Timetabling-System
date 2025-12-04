<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Records\Room;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'room_name' => 'RM301',
                'room_type' => 'lecture',
                'course_type_exclusive_to' => 'none',
                'room_capacity' => 50
            ],
            [
                'room_name' => 'RM302',
                'room_type' => 'lecture',
                'course_type_exclusive_to' => 'none',
                'room_capacity' => 50
            ],
            [
                'room_name' => 'AVR',
                'room_type' => 'lecture',
                'course_type_exclusive_to' => 'none',
                'room_capacity' => 50
            ],
            [
                'room_name' => 'CLV1',
                'room_type' => 'comlab',
                'course_type_exclusive_to' => 'none',
                'room_capacity' => 50
            ],
            [
                'room_name' => 'CLV2',
                'room_type' => 'comlab',
                'course_type_exclusive_to' => 'none',
                'room_capacity' => 50
            ],
            [
                'room_name' => 'CLV3',
                'room_type' => 'comlab',
                'course_type_exclusive_to' => 'none',
                'room_capacity' => 50
            ],
            [
                'room_name' => 'gym1',
                'room_type' => 'lecture',
                'course_type_exclusive_to' => 'pe',
                'room_capacity' => 50,
            ],
            [
                'room_name' => 'gym2',
                'room_type' => 'lecture',
                'course_type_exclusive_to' => 'pe',
                'room_capacity' => 50,
            ],
            [
                'room_name' => 'gym3',
                'room_type' => 'lecture',
                'course_type_exclusive_to' => 'pe',
                'room_capacity' => 50,
            ],
            [
                'room_name' => 'gym4',
                'room_type' => 'lecture',
                'course_type_exclusive_to' => 'pe',
                'room_capacity' => 50,
            ],
            [
                'room_name' => 'gym5',
                'room_type' => 'lecture',
                'course_type_exclusive_to' => 'pe',
                'room_capacity' => 50,
            ],
            [
                'room_name' => 'main1',
                'room_type' => 'lecture',
                'course_type_exclusive_to' => 'nstp',
                'room_capacity' => 50,
            ],
            [
                'room_name' => 'main2',
                'room_type' => 'lecture',
                'course_type_exclusive_to' => 'nstp',
                'room_capacity' => 50,
            ],
            [
                'room_name' => 'main3',
                'room_type' => 'lecture',
                'course_type_exclusive_to' => 'nstp',
                'room_capacity' => 50,
            ],
            [
                'room_name' => 'main4',
                'room_type' => 'lecture',
                'course_type_exclusive_to' => 'nstp',
                'room_capacity' => 50,
            ],
            [
                'room_name' => 'main5',
                'room_type' => 'lecture',
                'course_type_exclusive_to' => 'nstp',
                'room_capacity' => 50,
            ],
        ];

        Room::insertOrIgnore($data);
    }
}
