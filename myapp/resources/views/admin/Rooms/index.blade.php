@extends('pages.app')

@section('title', 'Rooms')

@section('content')
    <h1>Rooms</h1>
    <a href="{{route('admin.rooms.create')}}">Create</a>
    <ul>
        @foreach($rooms as $room)
            <li class="flex gap-10">
                <p>{{$room->room_name}}</p>
                <p>{{$room->room_type}}</p>
                <p>{{$room->room_capacity}}</p>
                <a href="{{route('admin.rooms.show', $room)}}">View</a>
                <a href="{{route('admin.rooms.edit', $room)}}">Edit</a>
                <x-buttons.delete action="admin.rooms.destroy" :model="$room" item_name="room" />
            </li>
        @endforeach
    </ul>
@endsection
