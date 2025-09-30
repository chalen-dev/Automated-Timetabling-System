@extends('app')

@section('title', 'Create Specialization')

@section('content')
    <form action="{{route('records.specializations.store', $professor)}}" method="POST">
        @csrf

        <h3>Assign Courses to {{$professor->last_name}}, {{$professor->first_name}}</h3>

        @foreach($courses as $course)
            <x-input.checklist
                label="{{$course->course_title}}"
                name="courses[]"
                :options="$courses"
                :value="$course->id"
            />
        @endforeach
    </form>
@endsection
