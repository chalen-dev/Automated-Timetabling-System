@extends('pages.app')

@section('title', 'Course Details')

@section('content')
    <h1>Course details</h1>
    <p>Course ID: {{$course['id']}}</p>
    <p>Course Name: {{$course['course_title']}}</p>
    <a href="{{route('Courses.index')}}">Back</a>
@endsection
