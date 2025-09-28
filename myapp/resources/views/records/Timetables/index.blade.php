@extends('app')

@section('title', 'Timetables')

@section('content')
    <div class="flex flex-col h-[calc(100vh-55px)] justify-center items-center">
        <div class="flex flex-row">
            <h1>My Timetables</h1>
            <a href="{{route('records.timetables.create')}}">Create</a>
        </div>

        <ul>
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
    </div>

@endsection
