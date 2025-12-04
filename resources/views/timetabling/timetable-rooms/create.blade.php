@extends('app')

@section('title', 'Add Rooms')

@section('content')

    @php
        $selected = request()->input('rooms', []);
    @endphp

    <div class="w-full p-4 pl-40">

        <!-- Header: Title + Search -->
        <div class="flex justify-between items-center mb-6 ">
            <h1 class="text-xl font-bold text-white">Choose Rooms</h1>

            <livewire:input.search-bar :action="route('timetables.timetable-rooms.create', $timetable)" placeholder="Search rooms...">
                @foreach($selected as $id)
                    <input type="hidden" name="rooms[]" value="{{ $id }}">
                @endforeach
            </livewire:input.search-bar>
        </div>

        <!-- Form -->
        <form action="{{ route('timetables.timetable-rooms.store', $timetable) }}" method="POST">
            @csrf

            <!-- Buttons -->
            <div class="flex gap-4 mb-4">
                <button type="submit"
                        class="bg-yellow-500 text-[#5e0b0b] px-4 py-2 rounded-lg font-semibold shadow hover:bg-yellow-600 active:bg-yellow-700 transition-all duration-150">
                    Add
                </button>
                <a href="{{ route('timetables.timetable-rooms.index', $timetable) }}"
                   class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold shadow hover:bg-gray-300 hover:text-gray-900 active:bg-gray-400 transition-all duration-150">
                    Back
                </a>
            </div>

            <!-- Message -->
            @if(isset($message))
                <div class="text-red-500 mb-4">{{ $message }}</div>
            @endif

            <!-- Rooms Table -->
            <table class="w-full text-left border-separate border-spacing-0 bg-white rounded-lg shadow-md overflow-hidden">
                <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3 font-semibold">Room Name</th>
                    <th class="px-6 py-3 font-semibold">Room Type</th>
                    <th class="px-6 py-3 font-semibold">Course Type Exclusive To</th>
                    <th class="px-6 py-3 font-semibold">Room Capacity</th>
                    <th class="px-6 py-3 font-semibold">Class Days</th>
                    <th class="px-6 py-3 font-semibold text-center">Select</th>
                </tr>
                </thead>
                <tbody class="text-gray-700">
                @foreach($rooms as $room)
                    <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors cursor-pointer"
                        onclick="if(event.target.type !== 'checkbox') this.querySelector('input[type=checkbox]').click()"
                        @if(in_array($room->id, $selected)) style="background-color: #FEF3C7;" @endif>
                        <td class="px-6 py-3">{{ $room->room_name }}</td>
                        <td class="px-6 py-3">{{ $room->room_type }}</td>
                        <td class="px-6 py-3">{{ $room->course_type_exclusive_to }}</td>
                        <td class="px-6 py-3">{{ $room->room_capacity }}</td>
                        <td class="px-6 py-3">{{ $room->roomExclusiveDays?->pluck('exclusive_day')->implode(', ') ?: 'N/A' }}</td>
                        <td class="px-6 py-3 text-center">
                            <input type="checkbox"
                                   name="rooms[]"
                                   value="{{ $room->id }}"
                                {{ in_array($room->id, $selected) ? 'checked' : '' }}>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </form>
    </div>

@endsection
