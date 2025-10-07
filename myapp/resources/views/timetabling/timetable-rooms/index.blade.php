@extends('app')

@section('title', 'Assigned Rooms')

@section('content')
    <div class="w-full pl-40 p-4"> <!-- Added left padding for sidebar -->

        <!-- Header: Title + Search + Add Button -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex gap-20 items-center justify-between">
                <h1 class="text-xl font-bold mb-0 text-white">Assigned Rooms</h1>
                <x-search-bar.search-bar
                    :action="route('timetables.timetable-rooms.index', $timetable)"
                    placeholder="Search rooms, course, or days..."
                />
            </div>

            <a href="{{ route('timetables.timetable-rooms.create', $timetable) }}"
               class="bg-yellow-500 text-[#5e0b0b] px-4 py-2 rounded-lg font-semibold shadow hover:bg-yellow-600 active:bg-yellow-700 transition-all duration-150">
                Add
            </a>
        </div>

        <!-- Table -->
        <table class="w-full text-left border-separate border-spacing-0 bg-white rounded-lg shadow-md overflow-hidden">
            <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
            <tr>
                <th class="px-6 py-3 font-semibold">Room Name</th>
                <th class="px-6 py-3 font-semibold">Room Type</th>
                <th class="px-6 py-3 font-semibold">Course Type Exclusive To</th>
                <th class="px-6 py-3 font-semibold">Room Capacity</th>
                <th class="px-6 py-3 font-semibold">Class Days</th>
                <th class="px-6 py-3 font-semibold text-center">Action</th>
            </tr>
            </thead>
            <tbody class="text-gray-700">
            @foreach($rooms as $room)
                <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-3">{{ $room->room_name }}</td>
                    <td class="px-6 py-3">{{ $room->room_type }}</td>
                    <td class="px-6 py-3">{{ $room->course_type_exclusive_to }}</td>
                    <td class="px-6 py-3">{{ $room->room_capacity ?? 'N/A' }}</td>
                    <td class="px-6 py-3">{{ $room->roomExclusiveDays?->pluck('exclusive_day')->map(fn($day) => ucfirst($day))->implode(', ') ?: 'N/A' }}</td>
                    <td class="px-6 py-3 text-center">
                        <x-buttons.delete
                            action="timetables.timetable-rooms.destroy"
                            :params="[$timetable, $room]"
                            item_name="room"
                            btnType="icon"
                            class="text-gray-600 px-3 py-2 rounded-lg hover:bg-gray-200 hover:text-gray-800 active:bg-gray-300 transition-all duration-150 flex items-center justify-center w-10 h-10"
                        />
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
