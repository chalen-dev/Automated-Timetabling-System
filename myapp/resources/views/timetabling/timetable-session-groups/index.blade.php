@extends('app')

@section('title', $timetable->timetable_name . ' Class Sessions')

@section('content')
    <h1>{{$timetable->timetable_name}} Class Sessions</h1>
    <a href="{{route('timetables.session-groups.create', $timetable)}}">Add</a>
    <ul>
        @foreach($sessionGroups as $sessionGroup)
            <li>
                <p>{{$sessionGroup->group_name}}</p>
            </li>
        @endforeach
    </ul>
@endsection
