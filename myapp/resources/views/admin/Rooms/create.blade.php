@extends('app')

@section('title', 'Create Room')

@section('content')
    <h1>Create Room</h1>
    <form action="{{route('admin.rooms.store')}}" method="POST">
        @csrf

        <x-input.text
            label="Room Name"
            name="room_name"
        />

        <x-input.select
            label="Room Type"
            name="room_type"
            :options="$roomTypeOptions"
            default=""
        />

        <x-input.select
            label="Course Type Exclusive To"
            name="course_type_exclusive_to"
            :options="$courseTypeExclusiveToOptions"
            default="none"
        />

        <x-input.number
            label="Room Capacity"
            name="room_capacity"
            :default="0"
            :min="0"
            :max="50"
            :step="1"
        />

        <button type="submit">Create</button>
    </form>
    <a href="{{route('admin.rooms.index')}}">Back</a>
@endsection
