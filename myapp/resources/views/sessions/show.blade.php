@extends('pages.app')

@section('title', 'Session Details')

@section('content')
    <h1>Session details</h1>
    <p>Session name: {{$session['session_name']}}</p>
    <p>ID: {{$session['id']}}</p>
    <a href="{{route('sessions.index')}}">Back</a>
@endsection
