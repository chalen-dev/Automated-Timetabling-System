@extends('app')

@section('title', 'Edit Class Session')

@section('content')
    <h1>Edit Class Session</h1>
    <form action="{{route('timetables.session-groups.update', [$timetable, $sessionGroup])}}" method="POST">
        @csrf
        @method('PUT')

        <x-input.text
            label="Session Letter"
            name="session_name"
            :value="$sessionGroup->session_name"
        />

        <x-input.select
            label="Academic Program"
            name="academic_program_id"
            :options="$academic_program_options"
            :value="$sessionGroup->academic_program_id"
        />

        <x-input.select
            label="Year Level"
            name="year_level"
            :options="$year_level_options"
            :value="$sessionGroup->year_level"
        />

        <x-input.text-area
            label="Short Description"
            name="short_description"
            rows="4"
        />

        <button type="submit">Confirm</button>

        <a href="{{route('timetables.session-groups.index', $timetable)}}">Back</a>

    </form>
@endsection
