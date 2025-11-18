@extends('app')

@section('title', 'Edit Room')

@section('content')
    <div class="flex flex-col pt-[40px] pb-[40px] pr-[50px] pl-[50px] gap-[50px] justify-center items-center bg-white rounded-2xl shadow-2xl">
        <h1 class="font-bold text-[18px]">Edit Room</h1>
        <form action="{{ route('rooms.update', $room) }}" method="POST" class="flex flex-col gap-10 w-full">
            @csrf
            @method('PUT')

            <div class="flex justify-center gap-7 w-full">
                <div class="flex flex-col justify-center items-stretch gap-5 w-full">
                    <div class="flex flex-row gap-5 w-full">
                        <livewire:input.text
                            label="Room Name"
                            name="room_name"
                            :value="old('room_name', $room->room_name)"
                            class="flex-1"
                        />

                        <livewire:input.select
                            label="Room Type"
                            name="room_type"
                            :options="$roomTypeOptions"
                            default=""
                            :value="old('room_type', $room->room_type)"
                            class="flex-1"
                        />

                        <livewire:input.select
                            label="Course Type Exclusive To"
                            name="course_type_exclusive_to"
                            :options="$courseTypeExclusiveToOptions"
                            :value="old('course_type_exclusive_to', $room->course_type_exclusive_to)"
                            class="flex-1"
                        />

                        <livewire:input.number
                            label="Room Capacity"
                            name="room_capacity"
                            :default="0"
                            :min="0"
                            :max="50"
                            :step="1"
                            :value="old('room_capacity', $room->room_capacity)"
                            class="flex-1"
                        />
                    </div>
                </div>
            </div>

            <div class="flex flex-row w-full justify-between items-center mt-[40px]">
                <a href="{{ route('rooms.index') }}">
                    <button type="button" class="pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#aaa] text-[#fff] cursor-pointer font-[600]">
                        <span>Back</span>
                    </button>
                </a>

                <button type="submit" class="pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#5e0b0b] text-[#fff] cursor-pointer font-[600]">
                    <span>Update</span>
                </button>
            </div>
        </form>
    </div>
@endsection
