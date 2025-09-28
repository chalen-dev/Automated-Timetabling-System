@extends('pages.app')

@section('title', 'Create Room')

@section('content')
    <h1>Create Room</h1>
    <form action="{{route('admin.rooms.store')}}">
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
            default=""
        />

        <x-input.number
            label="Room Capacity"
            name="room_capacity"
            :default="0"
            :min="0"
            :max="50"
            :step="1"
        />

    </form>
@endsection
