<?php

namespace App\Http\Controllers\Timetabling;

use App\Helpers\Logger;
use App\Http\Controllers\Controller;
use App\Models\Records\Room;
use App\Models\Records\Timetable;
use App\Models\Timetabling\TimetableRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class TimetableRoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Timetable $timetable, Request $request)
    {
        $query = $timetable->rooms();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('room_name', 'like', "%{$search}%")
                    ->orWhere('room_type', 'like', "%{$search}%")
                    ->orWhere('room_capacity', 'like', "%{$search}%")
                    ->orWhere('course_type_exclusive_to', 'like', "%{$search}%");
            });
        }

        $rooms = $query->get();

        Logger::log('index', 'timetable rooms', [
            'timetable_id' => $timetable->id,
        ]);

        return view('timetabling.timetable-rooms.index', compact('timetable', 'rooms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Timetable $timetable, Request $request)
    {
        $assignedRoomIds = $timetable->rooms->pluck('id');
        $query = Room::whereNotIn('id', $assignedRoomIds);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('room_name', 'like', "%{$search}%")
                    ->orWhere('room_type', 'like', "%{$search}%")
                    ->orWhere('room_capacity', 'like', "%{$search}%")
                    ->orWhere('course_type_exclusive_to', 'like', "%{$search}%");
            });
        }

        $rooms = $query->get();
        $selected = $request->input('rooms', []);

        Logger::log('create', 'timetable rooms', [
            'timetable_id' => $timetable->id,
        ]);

        return view('timetabling.timetable-rooms.create', compact('timetable', 'rooms', 'selected'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Timetable $timetable)
    {
        $validatedData = $request->validate([
            'rooms' => 'array',
            'rooms.*' => 'exists:rooms,id',
        ]);

        $assignedRoomIds = $timetable->rooms->pluck('id');
        $rooms = Room::whereNotIn('id', $assignedRoomIds)->get();

        if (empty($validatedData['rooms'])) {
            return view('timetabling.timetable-rooms.index', [
                'timetable' => $timetable,
                'rooms' => $rooms,
                'message' => 'Must select a room.'
            ]);
        }

        $addedRooms = [];

        foreach($validatedData['rooms'] as $roomId) {
            $timetable->rooms()->attach($roomId);

            $room = Room::find($roomId);
            $addedRooms[] = $room->room_name;
        }

        Logger::log('store', 'timetable rooms', [
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name,
            'added_rooms' => $addedRooms,
        ]);

        return redirect()->route('timetables.timetable-rooms.index', $timetable);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Timetable $timetable, Room $room)
    {
        // Keep a copy of the room name for matching BEFORE detaching
        $roomName = trim((string) $room->room_name);


        /*
        |--------------------------------------------------------------------------
        | 1. LOAD XLSX (bucket first, local fallback) — SAME AS OTHERS
        |--------------------------------------------------------------------------
        */
        $disk = Storage::disk('facultime');
        $remotePath = "timetables/{$timetable->id}.xlsx";

        if ($disk->exists($remotePath)) {
            $tempPath = tempnam(sys_get_temp_dir(), 'tt_') . '.xlsx';
            file_put_contents($tempPath, $disk->get($remotePath));
            $writeBackToBucket = true;
        } else {
            $tempPath = storage_path("app/exports/timetables/{$timetable->id}.xlsx");
            $writeBackToBucket = false;

            if (!file_exists($tempPath)) {
                return redirect()
                    ->route('timetables.timetable-rooms.index', $timetable)
                    ->with('error', 'Timetable XLSX not found.');
            }
        }

        $madeChange = false;

        /*
        |--------------------------------------------------------------------------
        | 2. REMOVE ROOM COLUMN FROM ALL TIMETABLE SHEETS
        |--------------------------------------------------------------------------
        */
        try {
            $spreadsheet = IOFactory::load($tempPath);

            $normalize = function ($s) {
                $s = strtolower(trim((string) $s));
                return preg_replace('/\s+/', ' ', $s);
            };

            $targetNorm = $normalize($roomName);

            foreach ($spreadsheet->getWorksheetIterator() as $sheet) {

                $highestCol = Coordinate::columnIndexFromString(
                    $sheet->getHighestColumn()
                );

                for ($col = 2; $col <= $highestCol; $col++) {

                    $headerValue = trim((string) $sheet->getCell(
                        Coordinate::stringFromColumnIndex($col) . '1'
                    )->getValue());

                    if ($headerValue === '') {
                        continue;
                    }

                    if ($normalize($headerValue) === $targetNorm) {

                        $highestRow = $sheet->getHighestRow();
                        $newColIndex = 1;

                        // Rebuild sheet without the removed column
                        for ($c = 1; $c <= $highestCol; $c++) {

                            if ($c === $col) {
                                continue;
                            }

                            for ($r = 1; $r <= $highestRow; $r++) {

                                $sheet->setCellValue(
                                    Coordinate::stringFromColumnIndex($newColIndex) . $r,
                                    $sheet->getCell(
                                        Coordinate::stringFromColumnIndex($c) . $r
                                    )->getValue()
                                );
                            }

                            $newColIndex++;
                        }

                        // Remove leftover columns at the end
                        if ($newColIndex <= $highestCol) {
                            $sheet->removeColumnByIndex(
                                $newColIndex,
                                $highestCol - $newColIndex + 1
                            );
                        }

                        $madeChange = true;
                        break;
                    }
                }
            }

            $overviewSheets = ['Overview_1st', 'Overview_2nd'];

            foreach ($spreadsheet->getWorksheetIterator() as $sheet) {

                if (!in_array($sheet->getTitle(), $overviewSheets, true)) {
                    continue;
                }

                $highestRow = $sheet->getHighestRow();
                $highestCol = Coordinate::columnIndexFromString(
                    $sheet->getHighestColumn()
                );

                for ($r = 2; $r <= $highestRow; $r++) { // skip header row
                    for ($c = 1; $c <= $highestCol; $c++) {

                        $cell = Coordinate::stringFromColumnIndex($c) . $r;
                        $value = trim((string) $sheet->getCell($cell)->getValue());

                        if ($normalize($value) === $targetNorm) {
                            $sheet->setCellValue($cell, 'vacant');
                            $madeChange = true;
                        }
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 3. SAVE XLSX BACK
            |--------------------------------------------------------------------------
            */
            if ($madeChange) {
                IOFactory::createWriter($spreadsheet, 'Xlsx')->save($tempPath);

                if ($writeBackToBucket) {
                    $disk->put($remotePath, fopen($tempPath, 'r'));
                    unlink($tempPath);
                }
            }

        } catch (Throwable $e) {
            return redirect()
                ->route('timetables.timetable-rooms.index', $timetable)
                ->with('error', 'Failed to update timetable spreadsheet.');
        }

        /*
        |--------------------------------------------------------------------------
        | 4. DONE
        |--------------------------------------------------------------------------
        */
        $successMsg = 'Room detached from timetable' . ($madeChange ? ' and spreadsheet column removed.' : '.');

        // Detach room from timetable (preserve existing DB behavior)
        $timetable->rooms()->detach($room->id);

        return redirect()
            ->route('timetables.timetable-rooms.index', $timetable)
            ->with('success', $successMsg);
    }





}
