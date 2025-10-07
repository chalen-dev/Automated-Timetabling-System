@extends('app')

@section('title', $timetable->timetable_name)

@section('content')
    <div class="flex w-full flex-col justify-start">
        <h1>Timetable View</h1>
        <p>{{$timetable->timetable_name}} {{$timetable->semester}} semester ({{$timetable->academic_year}})</p>
    </div>
    <table>

    </table>

@endsection
