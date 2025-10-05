@extends('app')

@section('title', 'Add Sessions')

@section('content')
    <form action="{{route('timetables.session-groups.course-sessions.store', [$timetable, $sessionGroup])}}" method="POST">
        @csrf

        <h1>Choose Courses</h1>
        @if(isset($message))
            <div class="!text-red-500">{{$message}}</div>
        @endif

        <table>
            <thead>
                <tr>
                    <td>Course Title</td>
                    <td>Course Name</td>
                    <td>Course Type</td>
                    <td>Units</td>
                    <td>Duration</td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                @foreach($courses as $course)
                <tr>
                    <td>{{$course->course_title}}</td>
                    <td>{{$course->course_name}}</td>
                    <td>{{$course->course_type}}</td>
                    <td>{{$course->unit_load}}</td>
                    <td>{{$course->duration_type}}</td>
                    <td>
                        <input type="checkbox" name="courses[]" value="{{$course->id}}"/>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <button type="submit">Add</button>
        <a href="{{route('timetables.session-groups.index', $timetable)}}">Back</a>
    </form>

@endsection
