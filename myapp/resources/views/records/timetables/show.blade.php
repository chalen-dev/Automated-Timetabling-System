@extends('app')

@section('title', 'Timetable')

@section('content')
    <h1>Timetable</h1>
    <p>Timetable Name: {{$timetable->timetable_name}}</p>
    <p>Semester: {{$timetable->semester}}</p>
    <p>Academic Year: {{$timetable->academic_year}}</p>
    <p>Timetable Description: {{$timetable->timetable_description}}</p>
    <a href="{{route('records.timetables.index')}}">Back</a>
@endsection
