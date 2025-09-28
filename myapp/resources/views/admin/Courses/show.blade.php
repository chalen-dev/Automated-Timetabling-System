@extends('app')

@section('title', 'Course Details')

@section('content')
    <h1>Course details</h1>
    <p>Course Title: {{$course->course_title}}</p>
    <p>Course Name: {{$course->course_name}}</p>
    <p>Course Type: {{$course->course_type}}</p>
    <p>Class Hours: {{$course->class_hours}}</p>
    <p>Total Lecture Class Days: {{$course->total_lecture_class_days}}</p>
    <p>Total Laboratory Class Days: {{$course->total_laboratory_class_days}}</p>
    <p>Unit Load: {{$course->unit_load}}</p>
    <p>Duration Type: {{$course->duration_type}}</p>
    <a href="{{route('admin.courses.index')}}">Back</a>
@endsection
