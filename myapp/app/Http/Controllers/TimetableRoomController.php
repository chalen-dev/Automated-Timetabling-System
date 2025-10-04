<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Timetable;
use App\Models\TimetableRoom;
use Illuminate\Http\Request;

class TimetableRoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Timetable $timetable)
    {
        $rooms = $timetable->room;
        return view('timetabling.timetable-rooms.index', compact('timetable', 'rooms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Timetable $timetable)
    {
        $assignedRoomIds = $timetable->room;
        return view('timetabling.timetable-rooms.index', compact('timetable', 'assignedRoomIds'));
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

        foreach($validatedData['rooms'] as $roomId) {
            $timetable->rooms()->attach($roomId);
        }

        return redirect()->route('timetables.timetable-professors.index', $timetable);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Timetable $timetable, TimetableRoom $timetableRoom)
    {
        $timetable->rooms()->detach($timetableRoom->id);
        return redirect()->route('timetables.timetable-professors.index', $timetable);
    }
}
