<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomExclusiveDay;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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

        $rooms = \App\Models\Room::with('roomExclusiveDays')
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

        return view('records.rooms.index', compact('rooms', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roomTypeOptions = $this->roomTypeOptions;
        $courseTypeExclusiveToOptions = $this->courseTypeExclusiveToOptions;
        return view('records.rooms.create', compact('roomTypeOptions', 'courseTypeExclusiveToOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'room_name' => 'required|string|unique:rooms,room_name',
            'room_type' => 'required|string',
            'course_type_exclusive_to' => 'required|string',
            'room_capacity' => 'nullable|integer|min:0|max:50',
        ]);

        Room::create($validatedData);
        return redirect()->route('rooms.index')
            ->with('success', 'Room created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        return view('records.rooms.show', compact('room'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room)
    {
        $roomTypeOptions = $this->roomTypeOptions;
        $courseTypeExclusiveToOptions = $this->courseTypeExclusiveToOptions;
        return view('records.rooms.edit', compact('room', 'roomTypeOptions', 'courseTypeExclusiveToOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        $validatedData = $request->validate([
            'room_name' => 'required|string|unique:rooms,room_name,' . $room->id,
            'room_type' => 'required|string',
            'course_type_exclusive_to' => 'required|string',
            'room_capacity' => 'nullable|integer|min:0|max:50',
        ]);

        $room->update($validatedData);
        return redirect()->route('rooms.index')
            ->with('success', 'Room updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        $room->delete();
        return redirect()->route('rooms.index')
            ->with('success', 'Room deleted successfully.');
    }
}
