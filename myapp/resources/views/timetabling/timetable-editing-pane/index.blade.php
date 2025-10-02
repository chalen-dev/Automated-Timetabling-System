@extends('app')

@section('title', $timetable->timetable_name)

@section('content')
    <h1>Timetable Editing Pane</h1>
    <p>{{$timetable->timetable_name}}</p>
@endsection
