@extends('pages.app')

@section('title', 'Courses')

@section('content')
    <h1>Courses</h1>
    <a href="{{route('courses.create')}}">Create</a>
    <!--Test Code Start-->
    <ul>
        @foreach($courses as $course)
            <li class="flex gap-10">
                <p>{{$course['course_title']}}</p>
                <a href="{{route('courses.show', $course)}}">View</a>
                <a href="">Edit</a>
            </li>
        @endforeach
    </ul>

@endsection
