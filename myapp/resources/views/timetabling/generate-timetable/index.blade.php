@extends('app')

@section('title', 'Generate Timetable')

@section('content')
    <h1>Generate Timetable</h1>

    @if(session('success'))
        <div style="color: green">{{ session('success') }}</div>
    @endif

    <form action="{{ route('timetables.generate.post', $timetable) }}" method="POST">
        @csrf
        <button type="submit">Generate CSVs</button>
    </form>
@endsection
