@extends('app')

@section('title', 'Create Class Session')

@section('content')
    <h1>Create Class Section</h1>
    <form action="{{route('timetables.session-groups.create', $timetable)}}" method="post">
        @csrf

        <x-input.text
            label="Session Name"
            name="group_name"
        />


    </form>
@endsection
