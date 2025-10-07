@extends('app')

@section('title', 'Course Details')

@section('content')
    <div class="flex flex-col gap-[20px] pt-[40px] pb-[40px] pr-[50px] pl-[50px] justify-center items-center bg-white rounded-2xl shadow-2xl">
        <h1 class="font-bold text-[18px]">Course Info</h1>
        <div class="flex flex-row gap-[15px]">
            <div class="flex flex-col gap-[8px]">
                <p>Course Title</p>
                <p>Course Name</p>
                <p>Course Type</p>
                <p>Class Hours</p>
                <p>Total Lecture Class Days</p>
                <p>Total Laboratory Class Days</p>
                <p>Unit Load</p>
                <p>Duration Type</p>
            </div>
            <div class="flex flex-col gap-[8px]">
                <p>:</p>
                <p>:</p>
                <p>:</p>
                <p>:</p>
                <p>:</p>
                <p>:</p>
                <p>:</p>
                <p>:</p>
            </div>
            <div class="flex flex-col gap-[8px]">
                <p>{{$course->course_title}}</p>
                <p>{{$course->course_name}}</p>
                <p>{{$course->course_type}}</p>
                <p>{{$course->class_hours}}</p>
                <p>{{$course->total_lecture_class_days}}</p>
                <p>{{$course->total_laboratory_class_days}}</p>
                <p>{{$course->unit_load}}</p>
                <p>{{$course->duration_type}}</p>
            </div>
        </div>
        <a href="{{route('courses.index')}}" class="flex flex-row w-full justify-center">
            <button class="pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#aaa] text-[#fff] cursor-pointer font-[600] hover:bg-[#828282]">
                <span>Back</span>
            </button>
        </a>
    </div>
@endsection
