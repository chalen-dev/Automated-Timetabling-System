<?php

namespace App\Http\Controllers\Timetabling;

use App\Helpers\Logger;
use App\Http\Controllers\Controller;
use App\Models\Records\Room;
use App\Models\Records\Timetable;
use App\Models\Timetabling\TimetableRoom;
use Illuminate\Http\Request;
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

        // Detach room from timetable (preserve existing DB behavior)
        $timetable->rooms()->detach($room->id);

        $xlsxPath = storage_path("app/exports/timetables/{$timetable->id}.xlsx");
        $errorMessage = null;
        $madeChange = false;

        try {
            if (file_exists($xlsxPath)) {
                if (!is_writable($xlsxPath) && !is_writable(dirname($xlsxPath))) {
                    $errorMessage = "Timetable file or directory is not writable: {$xlsxPath}";
                } else {
                    $spreadsheet = IOFactory::load($xlsxPath);
                    $sheetCount = $spreadsheet->getSheetCount();

                    $normalize = function ($s) {
                        $s = strtolower(trim((string) $s));
                        return preg_replace('/\s+/', ' ', $s);
                    };

                    $targetNorm = $normalize($roomName);

                    for ($si = 0; $si < $sheetCount; $si++) {
                        $sheet = $spreadsheet->getSheet($si);
                        $table = $sheet->toArray(null, true, true, false);
                        if (empty($table) || !isset($table[0]) || !is_array($table[0])) {
                            continue;
                        }

                        $header = $table[0];
                        $colCount = count($header);

                        // header index 0 is Time column; rooms start at index 1
                        for ($c = 1; $c < $colCount; $c++) {
                            $raw = trim((string) ($header[$c] ?? ''));
                            if ($raw === '') {
                                continue;
                            }

                            if ($normalize($raw) === $targetNorm) {
                                // array index -> Excel col index (1-based)
                                $excelIndex = $c + 1;

                                // remove one column at $excelIndex (1 = A)
                                $sheet->removeColumnByIndex($excelIndex, 1);

                                $madeChange = true;
                                // header changed; stop scanning this sheet
                                break;
                            }
                        }
                    }

                    if ($madeChange) {
                        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                        $writer->save($xlsxPath);
                    }
                }
            } else {
                $errorMessage = "Timetable XLSX not found: {$xlsxPath}";
            }
        } catch (Throwable $e) {
            // Capture error message to display to user; do not use Logger/\Log
            $errorMessage = "Error while updating timetable file: " . $e->getMessage();
        }

        // Use session flash messages for user-visible errors / success
        if ($errorMessage) {
            return redirect()
                ->route('timetables.timetable-rooms.index', $timetable)
                ->with('error', $errorMessage);
        }

        $successMsg = 'Room detached from timetable' . ($madeChange ? ' and spreadsheet column removed.' : '.');
        return redirect()
            ->route('timetables.timetable-rooms.index', $timetable)
            ->with('success', $successMsg);
    }




}
