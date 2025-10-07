@extends('app')

@section('title', $sessionFullName)

@section('content')
    <div class="flex flex-col gap-[20px] pt-[40px] pb-[40px] px-[40px] justify-center items-center bg-white rounded-2xl shadow-2xl max-w-[600px] mx-auto">
        <h1 class="font-bold text-[18px]">Class Session Info</h1>

        <!-- Standard info -->
        <div class="flex flex-row gap-[15px] w-full">
            <!-- Labels -->
            <div class="flex flex-col gap-[8px] min-w-[150px] whitespace-nowrap">
                <p>Session Letter</p>
                <p>Academic Program</p>
                <p>Year Level</p>
            </div>

            <!-- Colons -->
            <div class="flex flex-col gap-[8px] w-[10px]">
                <p>:</p>
                <p>:</p>
                <p>:</p>
            </div>

            <!-- Values -->
            <div class="flex flex-col gap-[8px] flex-1 break-words">
                <p>{{ $sessionGroup->session_name }}</p>
                <p>{{ $sessionGroup->academicProgram->program_abbreviation ?? 'Unknown' }}</p>
                <p>{{ $sessionGroup->year_level }}</p>
            </div>
        </div>

        <!-- Description as block -->
        <div class="flex flex-col gap-[5px] w-full">
            <p class="font-semibold">Short Description:</p>
            <p class="bg-gray-100 p-3 rounded-lg break-words">{{ $sessionGroup->short_description }}</p>
        </div>

        <a href="{{ route('timetables.session-groups.index', $timetable) }}" class="flex flex-row w-full justify-center">
            <button class="pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#aaa] text-[#fff] cursor-pointer font-[600] hover:bg-[#828282]">
                <span>Back</span>
            </button>
        </a>
    </div>
@endsection
