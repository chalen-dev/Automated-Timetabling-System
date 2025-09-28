@extends('app')

@section('title', 'Edit Timetable')

@section('content')
    <h1>Edit Timetable</h1>
    <form action="{{route('records.timetables.update', $timetable)}}" method="POST">
        @csrf
        @method('PUT')

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

        <button type="submit">Confirm Changes</button>

    </form>
    <a href="{{route('records.timetables.index')}}">Back</a>
@endsection
