@extends('pages.app')

@section('title', 'Sessions')

@section('content')
    <h1>Sessions</h1>
    <ul>
        @foreach($sessions as $session)
            <li>
                <p>{{$session['session_name']}}</p>
                <a href='{{route('ClassSections.show', $session['id'])}}'>View details</a>
            </li>
        @endforeach
    </ul>

@endsection
