<?php

namespace App\Http\Controllers\Records;

use App\Http\Controllers\Controller;
use App\Models\Records\Room;
use Illuminate\Http\Request;

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

        $this->logAction('viewed_rooms_list', ['search' => $search]);

        return view('records.rooms.index', compact('rooms', 'search'));
    }

    public function create()
    {
        $roomTypeOptions = $this->roomTypeOptions;
        $courseTypeExclusiveToOptions = $this->courseTypeExclusiveToOptions;

        $this->logAction('accessed_create_room_form');

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

        $this->logAction('create_room', [
            'room_id' => $room->id,
            'room_name' => $room->room_name
        ]);

        return redirect()->route('rooms.index')
            ->with('success', 'Room created successfully.');
    }

    public function show(Room $room)
    {
        $this->logAction('viewed_room', [
            'room_id' => $room->id,
            'room_name' => $room->room_name
        ]);

        return view('records.rooms.show', compact('room'));
    }

    public function edit(Room $room)
    {
        $roomTypeOptions = $this->roomTypeOptions;
        $courseTypeExclusiveToOptions = $this->courseTypeExclusiveToOptions;

        $this->logAction('accessed_edit_room_form', [
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

        $this->logAction('update_room', [
            'room_id' => $room->id,
            'room_name' => $room->room_name
        ]);

        return redirect()->route('rooms.index')
            ->with('success', 'Room updated successfully.');
    }

    public function destroy(Room $room)
    {
        $roomData = [
            'room_id' => $room->id,
            'room_name' => $room->room_name
        ];

        $room->delete();

        $this->logAction('delete_room', $roomData);

        return redirect()->route('rooms.index')
            ->with('success', 'Room deleted successfully.');
    }

    protected function logAction(string $action, array $details = [])
    {
        if(auth()->check()) {
            \App\Models\Records\UserLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'description' => json_encode($details),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }
}
