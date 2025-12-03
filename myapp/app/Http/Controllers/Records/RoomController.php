<?php

namespace App\Http\Controllers\Records;

use App\Helpers\Logger;
use App\Http\Controllers\Controller;
use App\Models\Records\Room;
use App\Models\Records\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class RoomController extends Controller
{
    private $roomTypeOptions = [
        'lecture' => 'Lecture',
        'comlab' => 'Computer Lab',
    ];

    private $courseTypeExclusiveToOptions = [
        'none' => 'None',
        'pe' => 'PE',
        'nstp' => 'NSTP',
        'others' => 'Others',
    ];



    public function index(Request $request)
    {
        $search = $request->input('search');

        $rooms = Room::with('roomExclusiveDays')
            ->when($search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('room_name', 'like', "%{$search}%")
                        ->orWhere('course_type_exclusive_to', 'like', "%{$search}%");
                })
                    ->orWhereHas('roomExclusiveDays', function($q) use ($search) {
                        $q->where('exclusive_day', 'like', "%{$search}%");
                    });
            })
            ->get();

        //Log
        Logger::log('index', 'room', null);

        return view('records.rooms.index', compact('rooms', 'search'));
    }

    public function create()
    {
        $roomTypeOptions = $this->roomTypeOptions;
        $courseTypeExclusiveToOptions = $this->courseTypeExclusiveToOptions;

        //Log
        Logger::log('create', 'room', null);

        return view('records.rooms.create', compact('roomTypeOptions', 'courseTypeExclusiveToOptions'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'room_name' => 'required|string|unique:rooms,room_name',
            'room_type' => 'required|string',
            'course_type_exclusive_to' => 'required|string',
            'room_capacity' => 'nullable|integer|min:0|max:50',
        ]);

        $room = Room::create($validatedData);

        Logger::log('store', 'room', $validatedData);

        return redirect()->route('rooms.index')
            ->with('success', 'Room created successfully.');
    }

    public function show(Room $room)
    {
        Logger::log('show', 'professor', [
            'room_id' => $room->id,
            'room_name' => $room->room_name
        ]);

        return view('records.rooms.show', compact('room'));
    }

    public function edit(Room $room)
    {
        $roomTypeOptions = $this->roomTypeOptions;
        $courseTypeExclusiveToOptions = $this->courseTypeExclusiveToOptions;

        Logger::log('edit', 'room', [
            'room_id' => $room->id,
            'room_name' => $room->room_name
        ]);

        return view('records.rooms.edit', compact('room', 'roomTypeOptions', 'courseTypeExclusiveToOptions'));
    }

    public function update(Request $request, Room $room)
    {
        $validatedData = $request->validate([
            'room_name' => 'required|string|unique:rooms,room_name,' . $room->id,
            'room_type' => 'required|string',
            'course_type_exclusive_to' => 'required|string',
            'room_capacity' => 'nullable|integer|min:0|max:50',
        ]);

        $room->update($validatedData);

        Logger::log('update', 'room', $validatedData);

        return redirect()->route('rooms.index')
            ->with('success', 'Room updated successfully.');
    }

    public function destroy(Room $room)
    {
        $roomName = trim((string) $room->room_name);
        $errorMessage = null;
        $anyChanges = false;

        try {
            // Find all timetables that reference this room
            $timetables = Timetable::whereHas('rooms', function ($q) use ($room) {
                $q->where('rooms.id', $room->id);
            })->get();

            $normalize = function ($s) {
                $s = strtolower(trim((string) $s));
                return preg_replace('/\s+/', ' ', $s);
            };
            $targetNorm = $normalize($roomName);

            foreach ($timetables as $timetable) {
                $xlsxPath = storage_path("app/exports/timetables/{$timetable->id}.xlsx");

                if (!file_exists($xlsxPath)) {
                    // skip if no file for this timetable
                    continue;
                }

                if (!is_writable($xlsxPath) && !is_writable(dirname($xlsxPath))) {
                    // capture the problem, but continue to attempt other timetables
                    $errorMessage = "Timetable file or directory not writable: {$xlsxPath}";
                    continue;
                }

                // Load workbook
                $spreadsheet = IOFactory::load($xlsxPath);
                $sheetCount  = $spreadsheet->getSheetCount();
                $madeChangeForThis = false;

                for ($si = 0; $si < $sheetCount; $si++) {
                    $sheet = $spreadsheet->getSheet($si);
                    $table = $sheet->toArray(null, true, true, false);
                    if (empty($table) || !isset($table[0]) || !is_array($table[0])) {
                        continue;
                    }

                    $header = $table[0];
                    $colCount = count($header);

                    // header index 0 is Time; rooms start at index 1
                    for ($c = 1; $c < $colCount; $c++) {
                        $raw = trim((string) ($header[$c] ?? ''));
                        if ($raw === '') continue;

                        if ($normalize($raw) === $targetNorm) {
                            // array index -> excel column index (1-based)
                            $excelIndex = $c + 1;

                            // remove one column starting at this excel index
                            $sheet->removeColumnByIndex($excelIndex, 1);

                            $madeChangeForThis = true;
                            // header changed; stop scanning this sheet
                            break;
                        }
                    }
                }

                if ($madeChangeForThis) {
                    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    $writer->save($xlsxPath);
                    $anyChanges = true;
                }
            }

            // Remove all pivot rows referencing this room (so timetables no longer reference it)
            DB::table('timetable_rooms')->where('room_id', $room->id)->delete();

            // Finally delete the Room record
            $room->delete();

        } catch (Throwable $e) {
            // surface a user-visible error; don't expose full trace
            $errorMessage = "Error while removing room from timetables: " . $e->getMessage();
        }

        if ($errorMessage) {
            return redirect()
                ->route('rooms.index')
                ->with('error', $errorMessage);
        }

        $msg = 'Room deleted successfully.' . ($anyChanges ? ' Spreadsheet columns removed where present.' : '');
        return redirect()
            ->route('rooms.index')
            ->with('success', $msg);
    }


}
