<?php

namespace App\Http\Controllers\Records;

use App\Helpers\Logger;
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

        //Log
        Logger::log('index', 'professor', null);

        return view('records.rooms.index', compact('rooms', 'search'));
    }

    public function create()
    {
        $roomTypeOptions = $this->roomTypeOptions;
        $courseTypeExclusiveToOptions = $this->courseTypeExclusiveToOptions;

        //Log
        Logger::log('create', 'professor', null);

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

        Logger::log('store', 'professor', $validatedData);

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

        Logger::log('edit', 'professor', [
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

        Logger::log('update', 'professor', $validatedData);

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

        Logger::log('delete', 'professor', $roomData);

        return redirect()->route('rooms.index')
            ->with('success', 'Room deleted successfully.');
    }

}
