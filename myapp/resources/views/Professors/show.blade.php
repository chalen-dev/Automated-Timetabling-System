@extends('pages.app')

@section('title', 'Professor Details')

@section('content')
    <h1>Professor Details</h1>
    <p>First Name: {{$professor->first_name}}</p>
    <p>Last Name: {{$professor->last_name}}</p>
    <p>Professor Type: {{$professor->professor_type}}</p>
    <p>Max Unit Load: {{$professor->max_unit_load}}</p>
    <p>Professor Age: {{$professor->professor_age ?? 'N/A'}}</p>
    <p>Position: {{$professor->position ?? 'N/A'}}</p>
    <a href="{{route('professors.index')}}">Back</a>
@endsection

