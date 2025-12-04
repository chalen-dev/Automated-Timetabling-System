@extends('app')

@section('title', 'Academic Program Details')

@section('content')
    <div class="flex flex-col gap-[20px] pt-[40px] pb-[40px] px-[40px] justify-center items-center bg-white rounded-2xl shadow-2xl max-w-[600px] mx-auto">
        <h1 class="font-bold text-[18px]">Academic Program Info</h1>

        <!-- Info rows -->
        <div class="flex flex-col gap-[12px] w-full">
            <!-- Program Name -->
            <div class="flex flex-row gap-[10px] w-full">
                <p class="min-w-[150px] whitespace-nowrap font-semibold">Program Name</p>
                <p class="w-[10px]">:</p>
                <p class="flex-1 break-words">{{$academicProgram->program_name}}</p>
            </div>

            <!-- Abbreviation -->
            <div class="flex flex-row gap-[10px] w-full">
                <p class="min-w-[150px] whitespace-nowrap font-semibold">Abbreviation</p>
                <p class="w-[10px]">:</p>
                <p class="flex-1 break-words">{{$academicProgram->program_abbreviation}}</p>
            </div>

            <!-- Description -->
            <div class="flex flex-col gap-[5px] w-full">
                <p class="font-semibold">Description:</p>
                <p class="bg-gray-100 p-3 rounded-lg break-words">{{$academicProgram->program_description}}</p>
            </div>
        </div>

        <a href="{{route('academic-programs.index')}}" class="flex flex-row w-full justify-center">
            <button class="pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#aaa] text-[#fff] cursor-pointer font-[600] hover:bg-[#828282]">
                <span>Back</span>
            </button>
        </a>
    </div>
@endsection
