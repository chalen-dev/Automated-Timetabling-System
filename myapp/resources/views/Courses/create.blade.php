@extends('pages.app')

@section('title', 'Create Course')

@section('content')
    <form action="{{route('courses.store')}}" method="POST" class="flex flex-col gap-4 justify-start">
        @csrf

        <x-text-input label="Course Title" name="course_title" />
        <x-text-input label="Course Name" name="course_name" />
        <x-select-input
            label="Course Type"
            name="course_type"
            :options="[
                'major' => 'Major',
                'minor' => 'Minor',
                'pe' => 'PE',
                'nstp' => 'NSTP',
                'other' => 'Other'
            ]"
            default="minor"
        />



        <button type="submit">Create</button>


    </form>
    <a href="{{route('courses.index')}}">Back</a>

@endsection
