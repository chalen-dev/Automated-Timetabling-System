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
    public function index(Timetable $timetable, Request $request)
    {
        $query = $timetable->rooms();

        // Optional search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('room_name', 'like', "%{$search}%")
                    ->orWhere('room_type', 'like', "%{$search}%")
                    ->orWhere('room_capacity', 'like', "%{$search}%")
                    ->orWhere('course_type_exclusive_to', 'like', "%{$search}%");
            });
        }

        $rooms = $query->get();

        return view('timetabling.timetable-rooms.index', compact('timetable', 'rooms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Timetable $timetable, Request $request)
    {
        $assignedRoomIds = $timetable->rooms->pluck('id');

        $query = Room::whereNotIn('id', $assignedRoomIds);

        // Optional: search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('room_name', 'like', "%{$search}%")
                    ->orWhere('room_type', 'like', "%{$search}%")
                    ->orWhere('room_capacity', 'like', "%{$search}%")
                    ->orWhere('course_type_exclusive_to', 'like', "%{$search}%");
            });
        }

        $rooms = $query->get();

        // Keep track of currently selected checkboxes from query string
        $selected = $request->input('rooms', []);

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

        foreach($validatedData['rooms'] as $roomId) {
            $timetable->rooms()->attach($roomId);
        }

        return redirect()->route('timetables.timetable-rooms.index', $timetable);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Timetable $timetable, TimetableRoom $timetableRoom)
    {
        $timetable->rooms()->detach($timetableRoom->id);
        return redirect()->route('timetables.timetable-rooms.index', $timetable);
    }
}
