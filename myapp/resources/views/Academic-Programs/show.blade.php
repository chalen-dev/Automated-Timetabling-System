@extends('pages.app')

@section('title', 'Academic Program Details')

@section('content')
    <h1>Academic Program Details</h1>
    <p>Program Name: {{$academicProgram->program_name}}</p>
    <p>Abbreviation: {{$academicProgram->program_abbreviation}}</p>
    <p>Description</p>
    <p>{{$academicProgram->program_description}}</p>
    <a href="{{route('academic-program.index')}}">Back</a>
@endsection
