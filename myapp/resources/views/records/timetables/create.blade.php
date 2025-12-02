@extends('app')

@section('title', 'Create Timetable')

@section('content')
    <div class="flex flex-col pb-[40px] pr-[50px] pl-[50px] pt-[40px] justify-center items-center bg-white rounded-2xl shadow-2xl" >
        <form class = "flex flex-col gap-[50px] items-center" action="{{route('timetables.store')}}" method="POST">
            @csrf
            <h1 class="font-bold text-[18px]">Create Timetable</h1>
            <div class="flex flex-col">
                <div class="flex gap-7">
                    <livewire:input.text
                        label="Name of Timetable"
                        name="timetable_name"
                        isRequired
                    />

                    <livewire:input.radio-group
                        label="Semester"
                        name="semester"
                        :options="$semesterOptions"
                        default=""
                        isRequired
                    />

                    <livewire:input.text
                        label="Academic Year"
                        name="academic_year"
                        isRequired
                    />
                </div>
                <div>
                    <livewire:input.text-area
                        label="Description"
                        name="timetable_description"
                        rows="4"
                    />
                </div>
            </div>
            <div class="flex flex-row w-full justify-between items-center">
                <livewire:buttons.back
                    :route="'timetables.index'"
                />
                <livewire:buttons.create submit/>
            </div>
        </form>
    </div>

@endsection
