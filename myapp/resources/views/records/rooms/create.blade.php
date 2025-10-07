@extends('app')

@section('title', 'Create Room')

@section('content')
    <div class="flex flex-col pt-[40px] pb-[40px] pr-[50px] pl-[50px] gap-[50px] justify-center items-center bg-white rounded-2xl shadow-2xl w-full max-w-[1200px]">
        <h1 class="font-bold text-[18px]">Create Room</h1>

        <form action="{{ route('rooms.store') }}" method="POST" class="flex flex-col gap-10 w-full">
            @csrf

            <div class="flex justify-center gap-7 w-full">
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

            <div class="flex flex-row w-full justify-between mt-[40px]">
                <a href="{{ route('rooms.index') }}">
                    <button type="button" class="pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#aaa] text-[#fff] cursor-pointer font-[600]">
                        <span>Back</span>
                    </button>
                </a>

                <button type="submit" class="pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#5e0b0b] text-[#fff] cursor-pointer font-[600]">
                    <span>Create</span>
                </button>
            </div>
        </form>
    </div>
@endsection
