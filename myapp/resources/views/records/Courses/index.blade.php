@extends('app')

@section('title', 'Courses')

@section('content')
    <h1>Courses</h1>
    <a href="{{route('records.courses.create')}}">Create</a>
    <ul>
        @foreach($courses as $course)
            <li class="flex gap-10">
                <p>{{$course->course_title}}</p>
                <p>{{$course->course_name}}</p>
                <p>{{$course->course_type}}</p>
                <p>{{$course->duration_type}}</p>
                <a href="{{route('records.courses.show', $course)}}">View</a>
                <a href="{{route('records.courses.edit', $course)}}">Edit</a>
                <x-buttons.delete action="admin.courses.destroy" :model='$course' item_name='course'/>
            </li>
        @endforeach
    </ul>

@endsection
