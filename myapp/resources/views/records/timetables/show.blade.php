@extends('app')

@section('title', 'Timetables')

@section('content')
    <div class="flex flex-col gap-[20px] pt-[40px] pb-[40px] pr-[50px] pl-[50px] justify-center items-center bg-white rounded-2xl shadow-2xl">
        <h1 class="font-bold text-[18px]">Timetable Info</h1>
        <div class="flex flex-row gap-[15px]">
            <div>
                <p>Timetable Name</p>
                <p>Semester</p>
                <p>Academic Year</p>
                <p>Timetable Description</p>
            </div>
            <div>
                <p>:</p>
                <p>:</p>
                <p>:</p>
                <p>:</p>
            </div>
            <div>
                <p>{{$timetable->timetable_name}}</p>
                <p>{{$timetable->semester}}</p>
                <p>{{$timetable->academic_year}}</p>
                <p>{{$timetable->timetable_description}}</p>
            </div>
        </div>
        <a href="{{route('timetables.index')}}" class="flex flex-row w-full justify-center">
            <button class="pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#aaa] text-[#fff] cursor-pointer font-[600] hover:bg-[#828282]">
                <span>Back</span>
            </button>
        </a>
    </div>
@endsection
