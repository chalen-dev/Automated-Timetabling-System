@extends('app')

@section('title', 'Timetable Info')

@section('content')
    <div class="w-screen flex justify-center pt-[40px] pb-[40px]">
        <div class="w-full max-w-[600px] bg-white rounded-2xl shadow-2xl p-[40px] flex flex-col gap-[25px]">


        <h1 class="font-bold text-[18px] text-center">
                Timetable Info
            </h1>

            <!-- Info fields -->
            <div class="flex flex-col gap-[15px]">

                <div>
                    <p class="text-sm font-semibold text-gray-600">Timetable Name</p>
                    <p class="text-base">{{ $timetable->timetable_name }}</p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-600">Semester</p>
                    <p class="text-base">{{ $timetable->semester }}</p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-600">Academic Year</p>
                    <p class="text-base">{{ $timetable->academic_year }}</p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-600">Created By</p>
                    <p class="text-base">
                        {{
                            trim(
                                ($timetable->user?->first_name ?? '') . ' ' .
                                ($timetable->user?->last_name ?? '')
                            ) ?: ($timetable->user?->name ?? 'Unknown')
                        }}
                    </p>
                </div>

            </div>

            <!-- Description -->
            <div class="flex flex-col gap-[6px]">
                <p class="text-sm font-semibold text-gray-600">
                    Timetable Description
                </p>
                <div class="bg-gray-100 p-3 rounded-lg text-sm min-h-[40px]">
                    {{ $timetable->timetable_description ?: '—' }}
                </div>
            </div>

            <!-- Back button -->
            <a href="{{ route('timetables.index') }}" class="flex justify-center">
                <button
                    class="pt-[10px] pb-[10px] pl-[24px] pr-[24px] rounded-[12px]
                           text-[16px] bg-[#aaa] text-white font-[600]
                           hover:bg-[#828282] transition"
                >
                    Back
                </button>
            </a>

        </div>
    </div>
@endsection
