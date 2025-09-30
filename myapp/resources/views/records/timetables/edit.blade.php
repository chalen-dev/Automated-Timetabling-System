@extends('app')

@section('title', 'Edit Timetable')

@section('content')
    <div class="flex flex-col gap-10 justify-center items-center pl-20 pr-20">
        <h1>Edit Timetable</h1>
        <form class = "flex flex-row gap-7 w-full" action="{{route('records.timetables.update', $timetable)}}" method="POST">
            @csrf
            @method('PUT')

            <div class="flex flex-col">
                <div class="flex gap-7 w-full">
                    <x-input.text
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

                    <x-input.text
                        label="Academic Year"
                        name="academic_year"
                        :value="old('academic_year', $timetable->academic_year)"
                    />

                    <x-input.text-area
                        label="Description"
                        name="timetable_description"
                        rows="4"
                        :value="old('timetable_description', $timetable->timetable_description)"
                    />
                </div>
                <div class="flex flex-row w-full justify-between items-center">
                    <a href="{{route('records.timetables.index')}}">Back</a>
                    <button type="submit">Confirm Changes</button>
                </div>
            </div>




        </form>

    </div>

@endsection
