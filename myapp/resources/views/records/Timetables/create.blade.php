@extends('app')

@section('title', 'Create Timetable')

@section('content')
    <h1>Create Timetable</h1>
    <form action="{{route('records.timetables.store')}}" method="POST">
        @csrf

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

        <button type="submit">Create</button>
    </form>
    <a href="{{route('records.timetables.index')}}">Back</a>
@endsection
