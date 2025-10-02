<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomExclusiveDay;
use Illuminate\Http\Request;

class RoomExclusiveDayController extends Controller
{
    protected $exclusiveDays = [
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday',
    ];
    /**
     * Display a listing of the resource.
     */
    public function index(Room $room)
    {
        $assignedExclusiveDays = $room->roomExclusiveDays; // returns collection of RoomExclusiveDay models
        return view('records.room-exclusive-days.index', [
            'room' => $room,
            'assignedExclusiveDays' => $assignedExclusiveDays,
            'exclusiveDays' => $this->exclusiveDays,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Room $room)
    {
        //get all days already assigned to the room
        $assignedDays = RoomExclusiveDay::where('room_id', $room->id)->pluck('exclusive_day')->toArray();

        //get all unassigned days
        $unassignedDays = array_diff_key($this->exclusiveDays, array_flip($assignedDays));

        return view('records.room-exclusive-days.create', compact('room', 'unassignedDays'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Room $room)
    {

        $validatedData = $request->validate([
            'exclusive_days' => 'required|array',
            'exclusive_days.*' => 'in:' . implode(',', array_keys($this->exclusiveDays))
        ]);


        if (empty($validatedData['exclusive_days'])) {
            return view('records.room-exclusive-days.create', [
                'room' => $room,
                'unassignedDays' => $this->exclusiveDays,
                'message' => 'No exclusive days were selected for this room.'
            ]);
        }

        foreach($validatedData['exclusive_days'] as $exclusive_day) {
            $room->roomExclusiveDays()->firstOrCreate([
                'room_id' => $room->id,
                'exclusive_day' => $exclusive_day
            ]);
        }

        return redirect()->route('records.rooms.room-exclusive-days.index', $room)
            ->with('success', 'Exclusive days have been added successfully.');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room, RoomExclusiveDay $roomExclusiveDay)
    {
        if ($roomExclusiveDay->room_id !== $room->id) {
            abort(404);
        }

        $roomExclusiveDay->delete();

        return redirect()
            ->route('records.rooms.room-exclusive-days.index', $room)
            ->with('success', 'Exclusive day deleted successfully.');
    }
}
