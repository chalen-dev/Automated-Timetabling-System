@extends('app')

@section('title', 'Rooms')

@section('content')
    <div class="flex justify-between">
        <h1>Rooms</h1>
        <a href="{{route('records.rooms.create')}}">Create</a>
    </div>

    <table class="w-full">
        <thead>
            <tr>
                <td>Room Name</td>
                <td>Room Type</td>
                <td>Room Capacity</td>
                <td></td>
            </tr>
        </thead>
        <tbody>
            @foreach($rooms as $room)
            <tr>
                <td>{{$room->room_name}}</td>
                <td>{{$room->room_type}}</td>
                <td>{{$room->room_capacity}}</td>
                <td class = "flex flex-row gap-4 justify-end">
                    <a class = 'flex items-center justify-center w-10 h-10' href="{{route('records.rooms.show', $room)}}">
                        <i class="bi-card-list"></i>
                    </a>
                    <a class = 'flex items-center justify-center w-10 h-10' href="{{route('records.rooms.edit', $room)}}">
                        <i class="bi bi-pencil-square"></i>
                    </a>

                    <x-buttons.delete action="records.rooms.destroy" :model='$room' item_name='room' btnType='icon'/>
                </td>
            </tr>

            @endforeach
        </tbody>
    </table>
@endsection
