@extends('app')

@section('title', 'Create Timetable')

@section('content')
    <div class="flex flex-col pb-[40px] pr-[50px] pl-[50px] justify-center items-center bg-white rounded-2xl shadow-2xl" >
        <form class = "flex flex-col gap-[50px] items-center" action="{{route('timetables.store')}}" method="POST">
            @csrf
            <h1 class="font-bold text-[18px]">Create Timetable</h1>
            <div class="flex flex-col">
                <div class="flex gap-7">
                    <x-input.text
                        label="Name of Timetable"
                        name="timetable_name"
                    />

                    <x-input.radio-group
                        label="Semester"
                        name="semester"
                        :options="$semesterOptions"
                        default=""
                    />

                    <x-input.text
                        label="Academic Year"
                        name="academic_year"
                    />

                    <x-input.text-area
                        label="Description"
                        name="timetable_description"
                        rows="4"
                    />
                </div>
            </div>
            <div class="flex flex-row w-full justify-between items-center">
                <a href="{{route('timetables.index')}}" class="pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#aaa] text-[#fff] cursor-pointer font-[600] hover:bg-[#828282]">
                        <span>Back</span>
                </a>
                <button type="submit" class="pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#5e0b0b] text-[#fff] cursor-pointer font-[600]"><span>Create</span></button>
            </div>
        </form>
    </div>

@endsection
