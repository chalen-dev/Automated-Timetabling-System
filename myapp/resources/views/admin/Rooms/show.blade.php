@extends('pages.app')

@section('title', 'Room Details')

@section('content')
    <h1>Room Details</h1>
    <p>Room Number: {{$room->room_number}}</p>
    <p>Room Type: {{$room->room_type}}</p>
    <p>Capacity: {{$room->capacity}}</p>
    <p>Specific Days: {{$room->specific_days}}</p>
    <a href="{{route('admin.rooms.index')}}">Back</a>
@endsection
