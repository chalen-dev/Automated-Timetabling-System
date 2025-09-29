@extends('app')

@section('title', 'Edit Room')

@section('content')
    <div class="flex flex-col gap-5 justify-center items-center">
        <h1>Edit Room</h1>
        <form action="{{route('records.rooms.update', $room)}}" method="POST">
            @csrf
            @method('PUT')
            <div class="flex flex-col gap-5">
                <div class="flex flex-row gap-5">
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
                </div>
                <div class="flex justify-between">
                    <a href="{{route('records.rooms.index')}}">Back</a>
                    <button type="submit">Update</button>
                </div>

            </div>
        </form>

    </div>

@endsection
