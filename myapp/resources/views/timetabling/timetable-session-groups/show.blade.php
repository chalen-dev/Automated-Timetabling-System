@extends('app')

@section('title', $sessionFullName)

@section('content')
    <h1>Class Session</h1>
    <h2>Session Letter</h2>
    <p>{{ $sessionGroup->session_name }}</p>

    <h2>Academic Program</h2>
    <p>{{ $sessionGroup->academicProgram->program_abbreviation ?? 'Unknown' }}</p>

    <h2>Year Level</h2>
    <p>{{ $sessionGroup->year_level }}</p>

    <h2>Short Description</h2>
    <p>{{ $sessionGroup->short_description }}</p>
    <a href="{{route('timetables.session-groups.index', $timetable)}}">Back</a>
@endsection

