@extends('app')

@section('title', 'Room Exclusive Days')

@section('content')
    <h1>Exclusive Days for {{$room->room_name}}</h1>
    <a href="{{route('records.rooms.room-exclusive-days.create', $room)}}">Add</a>
    <table class="w-full">
        <thead>
            <tr>
                <td>Day</td>
                <td></td>
            </tr>
        </thead>
        <tbody>
        @foreach($assignedExclusiveDays as $day) {{-- $day is a RoomExclusiveDay model --}}
            <tr>
                <td>{{ $exclusiveDays[$day->exclusive_day] }}</td>
                <td>
                    <x-buttons.delete
                        action="records.rooms.room-exclusive-days.destroy"
                        :params="[$room, $day]" {{-- this passes the model, so route gets /room-exclusive-days/{id} --}}
                        item_name="room exclusive day"
                        btnType="icon"
                    />
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <a href="{{route('records.rooms.index')}}">Back</a>
@endsection
