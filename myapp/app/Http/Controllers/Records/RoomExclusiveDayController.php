<?php

namespace App\Http\Controllers\Records;

use App\Http\Controllers\Controller;
use App\Models\Records\Room;
use App\Models\Records\RoomExclusiveDay;
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

        $this->logAction('viewed_room_exclusive_days', [
            'room_id' => $room->id,
            'assigned_days' => $assignedExclusiveDays->pluck('exclusive_day')->toArray()
        ]);

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
        // Get all days already assigned to the room
        $assignedDays = RoomExclusiveDay::where('room_id', $room->id)->pluck('exclusive_day')->toArray();

        // Get all unassigned days
        $unassignedDays = array_diff_key($this->exclusiveDays, array_flip($assignedDays));

        $this->logAction('accessed_create_room_exclusive_day_form', [
            'room_id' => $room->id,
            'unassigned_days' => array_values($unassignedDays)
        ]);

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

        $addedDays = [];
        foreach ($validatedData['exclusive_days'] as $exclusive_day) {
            $roomExclusiveDay = $room->roomExclusiveDays()->firstOrCreate([
                'room_id' => $room->id,
                'exclusive_day' => $exclusive_day
            ]);

            if ($roomExclusiveDay->wasRecentlyCreated) {
                $addedDays[] = $exclusive_day;
            }
        }

        $this->logAction('added_room_exclusive_days', [
            'room_id' => $room->id,
            'added_days' => $addedDays
        ]);

        return redirect()->route('rooms.room-exclusive-days.index', $room)
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

        $deletedDay = $roomExclusiveDay->exclusive_day;
        $roomExclusiveDay->delete();

        $this->logAction('deleted_room_exclusive_day', [
            'room_id' => $room->id,
            'deleted_day' => $deletedDay
        ]);

        return redirect()
            ->route('rooms.room-exclusive-days.index', $room)
            ->with('success', 'Exclusive day deleted successfully.');
    }

    /**
     * Log user actions.
     */
    protected function logAction(string $action, array $details = [])
    {
        if (auth()->check()) {
            \App\Models\Users\UserLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'description' => json_encode($details),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }
}
