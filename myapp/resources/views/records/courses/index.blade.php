@extends('app')

@section('title', 'Courses')

@section('content')
    <div class="flex justify-between">
        <h1>List of Courses</h1>
        <a href="{{route('records.courses.create')}}">Create</a>
    </div>
    <table class="w-full">
        <thead>
            <tr>
                <td>Course Title</td>
                <td>Course Name</td>
                <td>Course Type</td>
                <td>Duration</td>
                <td>Units</td>
                <td></td>
            </tr>
        </thead>
        <tbody>
            @foreach($courses as $course)
            <tr >
                <td>{{$course->course_title}}</td>
                <td>{{$course->course_name}}</td>
                <td>{{$course->course_type}}</td>
                <td>{{$course->duration_type}}</td>
                <td>{{$course->unit_load}}</td>
                <td class="whitespace-nowrap px-2">
                    <div class="flex flex-row gap-2 justify-end">
                        <a class = 'flex items-center justify-center w-10 h-10' href="{{route('records.courses.show', $course)}}">
                            <i class="bi-card-list"></i>
                        </a>
                        <a class = 'flex items-center justify-center w-10 h-10' href="{{route('records.courses.edit', $course)}}">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <x-buttons.delete action="records.courses.destroy" :params='$course' item_name='course' btnType='icon'/>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>



@endsection
