<?php

namespace App\Http\Controllers\Records;

use App\Helpers\Logger;
use App\Http\Controllers\Controller;
use App\Models\Records\AcademicProgram;
use App\Models\Records\Room;
use App\Models\Records\RoomExclusiveAcademicPrograms;
use Illuminate\Http\Request;

class RoomExclusiveAcademicProgramsController extends Controller
{
    /**
     * Display a listing of the resource.
     * Shows all academic programs that this room is exclusive to.
     */
    public function index(Room $room)
    {
        // We want the pivot model so we can delete by pivot id
        $assignedExclusivePrograms = RoomExclusiveAcademicPrograms::with('academicProgram')
            ->where('room_id', $room->id)
            ->get();

        Logger::log('index', 'room programs', [
            'room_id' => $room->id,
        ]);

        return view('records.room-exclusive-academic-programs.index', [
            'room' => $room,
            'assignedExclusivePrograms' => $assignedExclusivePrograms,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * Lists all academic programs that are not yet assigned to this room.
     */
    public function create(Room $room)
    {
        $assignedProgramIds = RoomExclusiveAcademicPrograms::where('room_id', $room->id)
            ->pluck('academic_program_id')
            ->toArray();

        $unassignedPrograms = AcademicProgram::query()
            ->when(!empty($assignedProgramIds), function ($q) use ($assignedProgramIds) {
                $q->whereNotIn('id', $assignedProgramIds);
            })
            ->orderBy('program_name')
            ->get();

        Logger::log('create', 'room programs', [
            'room_id' => $room->id,
        ]);

        return view('records.room-exclusive-academic-programs.create', compact('room', 'unassignedPrograms'));
    }

    /**
     * Store newly created exclusive academic programs for a room.
     */
    public function store(Request $request, Room $room)
    {
        $validatedData = $request->validate([
            'academic_program_ids'   => 'required|array',
            'academic_program_ids.*' => 'exists:academic_programs,id',
        ]);

        $programIds = $validatedData['academic_program_ids'] ?? [];

        if (empty($programIds)) {
            return view('records.room-exclusive-academic-programs.create', [
                'room' => $room,
                'unassignedPrograms' => AcademicProgram::orderBy('program_name')->get(),
                'message' => 'No academic programs were selected for this room.',
            ]);
        }

        // Avoid duplicates using syncWithoutDetaching
        $room->exclusiveAcademicPrograms()->syncWithoutDetaching($programIds);

        Logger::log('store', 'room programs', [
            'room_id' => $room->id,
            'added_program_ids' => $programIds,
        ]);

        return redirect()
            ->route('rooms.room-exclusive-academic-programs.index', $room)
            ->with('success', 'Exclusive academic programs have been added successfully.');
    }

    /**
     * Remove the specified exclusive academic program from a room.
     */
    public function destroy(Room $room, RoomExclusiveAcademicPrograms $roomExclusiveAcademicProgram)
    {
        if ($roomExclusiveAcademicProgram->room_id !== $room->id) {
            abort(404);
        }

        $program = $roomExclusiveAcademicProgram->academicProgram;
        $programName = $program ? $program->program_name : ('ID ' . $roomExclusiveAcademicProgram->academic_program_id);

        $roomExclusiveAcademicProgram->delete();

        Logger::log('delete', 'room programs', [
            'room_id' => $room->id,
            'academic_program' => $programName,
        ]);

        return redirect()
            ->route('rooms.room-exclusive-academic-programs.index', $room)
            ->with('success', 'Exclusive academic program removed successfully.');
    }
}
