@extends('app')

@section('title', 'Room Details')

@section('content')
    <h1>Room Details</h1>
    <p>Room Name: {{$room->room_name}}</p>
    <p>Room Type: {{$room->room_type}}</p>
    <p>Capacity: {{$room->room_capacity ?? 'N/A'}}</p>
    <p>Course Type Exclusive To: {{$room->course_type_exclusive_to}}</p>
    <a href="{{route('rooms.index')}}">Back</a>
@endsection
