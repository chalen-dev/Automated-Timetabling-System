@extends('app')

@section('title', 'Copy Timetable')

@section('content')
    <div class="flex flex-col pb-[40px] pr-[50px] pl-[50px] pt-[40px] justify-center items-center bg-white rounded-2xl shadow-2xl max-w-[800px] mx-auto mt-[40px]">
        <form class="flex flex-col gap-[50px] items-center w-full"
              action="{{ route('timetables.store-copy', $timetable) }}"
              method="POST">
            @csrf

            <h1 class="font-bold text-[18px]">Copy Timetable</h1>

            <div class="flex flex-col w-full gap-[25px]">
                <div class="flex gap-7 w-full">
                    <livewire:input.text
                        label="Name of Timetable"
                        name="timetable_name"
                        :value="old('timetable_name', $timetable->timetable_name . ' (Copy)')"
                        isRequired
                    />

                    <livewire:input.radio-group
                        label="Semester"
                        name="semester"
                        :options="$semesterOptions"
                        :value="old('semester', $timetable->semester)"
                        isRequired
                    />

                    <livewire:input.text
                        label="Academic Year"
                        name="academic_year"
                        :value="old('academic_year', $timetable->academic_year)"
                        isRequired
                    />
                </div>

                <livewire:input.text-area
                    label="Description"
                    name="timetable_description"
                    rows="4"
                    :value="old('timetable_description', $timetable->timetable_description)"
                />
            </div>

            <p class="text-xs text-gray-500 w-full">
                When you confirm, a new timetable will be created with the same
                <strong>Class Sessions, Course Sessions, <!--Professors,--> and Rooms</strong>
                as <strong>{{ $timetable->timetable_name }}</strong>.
                Scheduling data will start empty.
            </p>

            <div class="flex flex-row w-full justify-between items-center">
                <a href="{{ route('timetables.index') }}">
                    <button type="button"
                            class="pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#aaa] text-[#fff] font-[600]">
                        Back
                    </button>
                </a>

                <button type="submit"
                        class="pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#5e0b0b] text-[#fff] font-[600]">
                    Confirm Copy
                </button>
            </div>
        </form>
    </div>
@endsection
