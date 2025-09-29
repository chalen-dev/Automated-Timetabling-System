@extends('app')

@section('title', 'Create Room')

@section('content')
    <div class="flex flex-col gap-5 justify-center items-center">
        <h1>Create Room</h1>
        <form action="{{route('records.rooms.store')}}" method="POST">
            @csrf
            <div class="flex flex-col gap-5">
                <div class="flex flex-row gap-20">
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
                </div>
                <div class="flex justify-between">
                    <a href="{{route('records.rooms.index')}}">Back</a>
                    <button type="submit">Create</button>
                </div>
            </div>



        </form>

    </div>
@endsection
