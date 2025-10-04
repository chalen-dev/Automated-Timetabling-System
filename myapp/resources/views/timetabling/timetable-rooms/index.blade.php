@extends('app')

@section('title', 'Assigned Rooms')

@section('content')
    <h1>Assigned Rooms</h1>
    <a href="{{route('timetables.timetable-rooms.create', $timetable)}}">Add</a>
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
                <td>{{$room->room_name}}</td>
                <td>{{$room->room_type}}</td>
                <td>{{$room->course_type_exclusive_to}}</td>
                <td>{{$room->room_capacity}}</td>
                <td>{{ $room->roomExclusiveDays?->pluck('exclusive_day')->implode(', ') ?: 'N/A' }}</td>
                <td>
                    <x-buttons.delete
                        action="timetables.timetable-rooms.destroy"
                        :params="[$timetable, $room]"
                        item_name="room"
                        btn_type="icon"
                    />
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
