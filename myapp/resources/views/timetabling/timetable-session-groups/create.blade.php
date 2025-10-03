@extends('app')

@section('title', 'Create Class Session')

@section('content')
    <h1>Create Class Section</h1>
    <form action="{{route('timetables.session-groups.store', $timetable)}}" method="post">
        @csrf

        <x-input.text
            label="Session Name"
            name="session_name"
        />

        <x-input.select
            label="Academic Program"
            name="academic_program_id"
            :options="$academic_program_options"
        />

        <x-input.select
            label="Year Level"
            name="year_level"
            :options="$year_level_options"
        />

        <button type="submit">Add</button>


        <a href="{{route('timetables.session-groups.index', $timetable)}}">Back</a>
    </form>
@endsection
