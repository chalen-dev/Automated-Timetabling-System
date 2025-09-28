@extends('app')

@section('title', 'Edit Room')

@section('content')
    <h1>Edit Room</h1>
    <form action="{{route('admin.rooms.update', $room)}}" method="POST">
        @csrf
        @method('PUT')

        <x-input.text
            label="Room Name"
            name="room_name"
            :value="old('room_name', $room->room_name)"
        />

        <x-input.select
            label="Room Type"
            name="room_type"
            :options="$roomTypeOptions"
            default=""
            :value="old('room_type', $room->room_type)"
        />

        <x-input.select
            label="Course Type Exclusive To"
            name="course_type_exclusive_to"
            :options="$courseTypeExclusiveToOptions"
            :value="old('course_type_exclusive_to', $room->course_type_exclusive_to)"
        />

        <x-input.number
            label="Room Capacity"
            name="room_capacity"
            :default="0"
            :min="0"
            :max="50"
            :step="1"
            :value="old('room_capacity', $room->room_capacity)"
        />

        <button type="submit">Update</button>

    </form>
    <a href="{{route('admin.rooms.index')}}">Back</a>
@endsection
