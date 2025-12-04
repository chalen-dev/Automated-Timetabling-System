@extends('app')

@section('title', 'Room Details')

@section('content')
    <div class="flex flex-col gap-[20px] pt-[40px] pb-[40px] px-[40px] justify-center items-center bg-white rounded-2xl shadow-2xl max-w-[600px] mx-auto">
        <h1 class="font-bold text-[18px]">Room Info</h1>

        <div class="flex flex-row gap-[15px] w-full">
            <!-- Labels -->
            <div class="flex flex-col gap-[8px] min-w-[150px] whitespace-nowrap">
                <p>Room Name</p>
                <p>Room Type</p>
                <p>Capacity</p>
                <p>Course Type Exclusive To</p>
            </div>

            <!-- Colons -->
            <div class="flex flex-col gap-[8px] w-[10px]">
                <p>:</p>
                <p>:</p>
                <p>:</p>
                <p>:</p>
            </div>

            <!-- Values -->
            <div class="flex flex-col gap-[8px] flex-1 break-words">
                <p>{{$room->room_name}}</p>
                <p>{{$room->room_type}}</p>
                <p>{{$room->room_capacity ?? 'N/A'}}</p>
                <p>{{$room->course_type_exclusive_to}}</p>
            </div>
        </div>

        <a href="{{route('rooms.index')}}" class="flex flex-row w-full justify-center">
            <button class="pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#aaa] text-[#fff] cursor-pointer font-[600] hover:bg-[#828282]">
                <span>Back</span>
            </button>
        </a>
    </div>
@endsection
