@extends('app')

@section('title', 'Timetables')

@section('content')
    <h1>My Timetables</h1>

    <ul>
        <a href="{{route('records.timetables.create')}}">Create</a>
        @foreach($timetables as $timetable)
            <li class="flex gap-10">
                <p>{{$timetable->timetable_name}}</p>
                <p>{{$timetable->semester}} semester</p>
                <p>{{$timetable->academic_year}}</p>
                <a href="{{route('records.timetables.show', $timetable)}}">View</a>
                <a href="{{route('records.timetables.edit', $timetable)}}">Edit</a>
                <x-buttons.delete action="records.timetables.destroy" :model="$timetable" item_name="timetable"/>
            </li>
        @endforeach
    </ul>
@endsection
