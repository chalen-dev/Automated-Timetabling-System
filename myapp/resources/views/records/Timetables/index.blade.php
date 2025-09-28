@extends('app')

@section('title', 'Timetables')

@section('content')
    <div class="flex flex-col h-[calc(100vh-55px)]">
        <h1>My Timetables</h1>


        <ul class="flex flex-row gap-10">
            <li >

                <a class="flex justify-center items-center flex-col h-50 w-75 flex-wrap"
                   href="{{route('records.timetables.create')}}">
                    <span>+</span>
                    <span>Create New Timetable</span>
                </a>

            @foreach($timetables as $timetable)
                <li class="flex h-50 w-75 gap-5 pt-3 pb-7 pl-5 pr-5">
                    <div class="flex flex-col">
                        <p>{{$timetable->timetable_name}}</p>
                        <p>{{$timetable->semester}} semester ({{$timetable->academic_year}})</p>
                    </div>
                    <div class="flex flex-col gap-10">
                        <div class="flex flex-row">
                            <a class = 'flex items-center justify-center' href="{{route('records.timetables.show', $timetable)}}">
                                <i class="bi-card-list"></i>
                            </a>
                            <a class = 'flex items-center justify-center' href="{{route('records.timetables.edit', $timetable)}}">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <x-buttons.delete action="records.timetables.destroy" :model="$timetable" item_name="timetable" btnType="icon"/>
                        </div>
                        <div class="bg-red-500">

                        </div>

                    </div>

                </li>
            @endforeach
        </ul>
    </div>

@endsection
