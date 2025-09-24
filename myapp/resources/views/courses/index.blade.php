@extends('pages.app')

@section('title', 'Courses')

@section('content')
    <h1>Courses</h1>
    <!--Test Code Start-->
    <ul>
        @foreach($courses as $course)
            <li>
                <p>{{$course['course_title']}}</p>
                <a href="{{route('courses.show', $course['id'])}}">View details</a>
            </li>
        @endforeach
    </ul>

@endsection
