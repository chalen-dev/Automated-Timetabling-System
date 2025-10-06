@extends('app')

@section('title', 'Add Sessions')

@section('content')
    <h1>Choose Courses for {{ $sessionGroup->session_name }}</h1>

    {{-- Search bar for Courses --}}
    <x-search-bar.search-bar :action="route('timetables.session-groups.course-sessions.create', [$timetable, $sessionGroup])">
        {{-- Keep selected courses when searching --}}
        @foreach(old('courses', $selected ?? []) as $selectedCourseId)
            <input type="hidden" name="courses[]" value="{{ $selectedCourseId }}">
        @endforeach
    </x-search-bar.search-bar>

    <form action="{{ route('timetables.session-groups.course-sessions.store', [$timetable, $sessionGroup]) }}" method="POST">
        @csrf

        <button type="submit">Add</button>
        <a href="{{ route('timetables.session-groups.index', $timetable) }}">Back</a>

        @if(isset($message))
            <div class="!text-red-500">{{ $message }}</div>
        @endif

        <table class="w-full border">
            <thead>
            <tr>
                <th>Course Title</th>
                <th>Course Name</th>
                <th>Course Type</th>
                <th>Units</th>
                <th>Duration</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($courses as $course)
                <tr>
                    <td>{{ $course->course_title }}</td>
                    <td>{{ $course->course_name }}</td>
                    <td>{{ $course->course_type }}</td>
                    <td>{{ $course->unit_load }}</td>
                    <td>{{ $course->duration_type }}</td>
                    <td>
                        <input type="checkbox"
                               name="courses[]"
                               value="{{ $course->id }}"
                            {{ in_array($course->id, old('courses', $selected ?? [])) ? 'checked' : '' }}
                        />
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </form>
@endsection
