@extends('app')

@section('title', 'Add Rooms')

@section('content')

    @php
        $selected = request()->input('rooms', []);
    @endphp

    <h1>Choose Rooms</h1>

    <x-search-bar.search-bar :action="route('timetables.timetable-rooms.create', $timetable)">
        @foreach($selected as $id)
            <input type="hidden" name="rooms[]" value="{{ $id }}">
        @endforeach
    </x-search-bar.search-bar>

    <form action="{{ route('timetables.timetable-rooms.store', $timetable) }}" method="POST">
        @csrf

        <button type="submit">Add</button>
        <a href="{{ route('timetables.timetable-rooms.index', $timetable) }}">Back</a>

        @if(isset($message))
            <div class="!text-red-500">{{ $message }}</div>
        @endif

        <table class="w-full">
            <thead>
            <tr>
                <td>Room Name</td>
                <td>Room Type</td>
                <td>Course Type Exclusive To</td>
                <td>Room Capacity</td>
                <td>Class Days</td>
                <td></td>
            </tr>
            </thead>
            <tbody>
            @foreach($rooms as $room)
                <tr>
                    <td>{{ $room->room_name }}</td>
                    <td>{{ $room->room_type }}</td>
                    <td>{{ $room->course_type_exclusive_to }}</td>
                    <td>{{ $room->room_capacity }}</td>
                    <td>{{ $room->roomExclusiveDays?->pluck('exclusive_day')->implode(', ') ?: 'N/A' }}</td>
                    <td>
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
@endsection
