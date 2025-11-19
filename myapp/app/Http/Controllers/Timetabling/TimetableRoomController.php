<?php

namespace App\Http\Controllers\Timetabling;

use App\Helpers\Logger;
use App\Http\Controllers\Controller;
use App\Models\Records\Room;
use App\Models\Records\Timetable;
use App\Models\Timetabling\TimetableRoom;
use Illuminate\Http\Request;

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
    public function destroy(Timetable $timetable, TimetableRoom $timetableRoom)
    {
        $room = Room::find($timetableRoom->id);

        $timetable->rooms()->detach($timetableRoom->id);

      Logger::log('delete', 'timetable rooms', [
          'timetable_id' => $timetable->id,
          'timetable_name' => $timetable->timetable_name,
          'room_id' => $room->id,
          'room_name' => $room->room_name,
      ]);

        return redirect()->route('timetables.timetable-rooms.index', $timetable);
    }
}
