@extends('app')

@section('title', 'Create Timetable')

@section('content')
    <div class="flex flex-col gap-10 justify-center items-center pl-20 pr-20">
        <h1>Create Timetable</h1>
        <form class = "flex flex-row " action="{{route('timetables.store')}}" method="POST">
            @csrf

            <div class="flex flex-col">
                <div class="flex gap-7 w-full">
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
                <div class="flex flex-row w-full justify-between items-center">
                    <a href="{{route('timetables.index')}}">Back</a>
                    <button type="submit">Create</button>
                </div>
            </div>
        </form>



    </div>

@endsection
