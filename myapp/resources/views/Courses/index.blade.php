@extends('pages.app')

@section('title', 'Courses')

@section('content')
    <h1>Courses</h1>
    <a href="{{route('courses.create')}}">Create</a>
    <!--Test Code Start-->
    <ul>
        @foreach($courses as $course)
            <li class="flex gap-10">
                <p>{{$course->course_title}}</p>
                <p>{{$course->course_name}}</p>
                <p>{{$course->course_type}}</p>
                <p>{{$course->duration_type}}</p>
                <a href="{{route('courses.show', $course)}}">View</a>
                <a href="{{route('courses.edit', $course)}}">Edit</a>
                @include('Courses.delete')
            </li>
        @endforeach
    </ul>

@endsection
