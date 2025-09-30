@extends('app')

@section('title', 'Create Specialization')

@section('content')
    <form action="{{route('records.professors.specializations.store', $professor)}}" method="POST">
        @csrf

        <h3>Assign Courses to {{$professor->last_name}}, {{$professor->first_name}}</h3>
        @if(isset($message))
            <div class="!text-red-500">{{ $message }}</div>
        @endif
        <table class="w-full">
            <thead>
                <tr>
                    <td>Course Title</td>
                    <td>Course Name</td>
                </tr>
            </thead>
            <tbody>
                @foreach($courses as $course)
                <tr>
                    <td>{{$course->course_title}}</td>
                    <td>{{$course->course_name}}</td>
                    <td>
                        <input type="checkbox" name="courses[]" value="{{ $course->id }}">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <button type="submit">Confirm</button>
        <a href="{{route('records.professors.specializations.index', $professor)}}">Back</a>
    </form>
@endsection
