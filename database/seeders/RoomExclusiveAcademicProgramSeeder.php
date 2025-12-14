<?php

namespace Database\Seeders;

use App\Models\Records\Room;
use App\Models\Records\RoomExclusiveAcademicPrograms;
use Illuminate\Database\Seeder;

class RoomExclusiveAcademicProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * Map ROOM NAME => program IDs
         *
         * - null or [] => OPEN to all programs (no pivot rows)
         * - [1,2,...]  => EXCLUSIVE allow-list (only those programs)
         *
         * academic_program_id reference:
         * 1 = CS, 2 = IT, 3 = CpE, 4 = EE, 5 = ECE
         */
        $map = [
            // Computer labs reserved for computing programs (edit as needed)
            'CLV1' => [1, 2],
            'CLV2' => [1, 2],
            'CLV3' => [1, 2],

            'RM301' => [1, 2],
            'RM302' => [1, 2],
            'AVR' => [1, 2],

            // Everything else open by default
            'gym1' => null,
            'gym2' => null,
            'gym3' => null,
            'gym4' => null,
            'gym5' => null,

            'main1' => null,
            'main2' => null,
            'main3' => null,
            'main4' => null,
            'main5' => null,
        ];

        $rowsToInsert = [];

        foreach ($map as $roomName => $programIds) {
            $room = Room::where('room_name', $roomName)->first();
            if (!$room) {
                continue;
            }

            // OPEN to all: ensure no pivot rows exist (so reruns stay consistent)
            if ($programIds === null || (is_array($programIds) && count($programIds) === 0)) {
                RoomExclusiveAcademicPrograms::where('room_id', $room->id)->delete();
                continue;
            }

            // Restricted: reset and re-insert allow-list
            RoomExclusiveAcademicPrograms::where('room_id', $room->id)->delete();

            foreach ((array) $programIds as $programId) {
                $rowsToInsert[] = [
                    'room_id' => $room->id,
                    'academic_program_id' => (int) $programId,
                ];
            }
        }

        RoomExclusiveAcademicPrograms::insertOrIgnore($rowsToInsert);
    }
}
