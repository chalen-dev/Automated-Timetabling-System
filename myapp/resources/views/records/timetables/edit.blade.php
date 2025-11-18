@extends('app')

@section('title', 'Edit Timetable')

@section('content')
    <div class="flex flex-col pb-[40px] pr-[50px] pl-[50px] pt-[40px] justify-center items-center bg-white rounded-2xl shadow-2xl">
        <form class = "flex flex-col gap-[50px] items-center" action="{{route('timetables.update', $timetable)}}" method="POST">
            @csrf
            @method('PUT')
            <h1 class="font-bold text-[18px]">Edit Timetable</h1>
            <div class="flex flex-col">
                <div class="flex gap-7 w-full">
                    <livewire:input.text
                        label="Name of Timetable"
                        name="timetable_name"
                        :value="old('timetable_name', $timetable->timetable_name)"
                    />

                    <x-input.radio-group
                        label="Semester"
                        name="semester"
                        :options="$semesterOptions"
                        default=""
                        :value="old('semester', $timetable->semester)"
                    />

                    <livewire:input.text
                        label="Academic Year"
                        name="academic_year"
                        :value="old('academic_year', $timetable->academic_year)"
                    />
                </div>
                <div>
                    <x-input.text-area
                        label="Description"
                        name="timetable_description"
                        rows="4"
                        :value="old('timetable_description', $timetable->timetable_description)"
                    />
                </div>
            </div>
            <div class="flex flex-row w-full justify-between items-center">
                <a href="{{route('timetables.index')}}">
                    <button class="pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#aaa] text-[#fff] cursor-pointer font-[600] hover:bg-[#828282]">
                        <span>Back</span>
                    </button>
                </a>
                <button type="submit" class="pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#5e0b0b] text-[#fff] cursor-pointer font-[600]"><span>Save</span></button>
            </div>
        </form>
    </div>

@endsection
