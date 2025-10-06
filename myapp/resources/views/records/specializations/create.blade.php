@extends('app')

@section('title', 'Create Specialization')

@section('content')
    <h1>Assign Courses to {{ $professor->last_name }}, {{ $professor->first_name }}</h1>

    <!-- Search Bar -->
    <div class="flex justify-between mb-4">
        <x-search-bar.search-bar
            :action="route('professors.specializations.create', $professor)"
            placeholder="Search courses by title or name..."
            name="search"
        />
        <div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Add</button>
            <a href="{{ route('professors.specializations.index', $professor) }}" class="bg-gray-300 px-4 py-2 rounded">Back</a>
        </div>

    </div>

    @if(isset($message))
        <div class="text-red-500 mb-4">{{ $message }}</div>
    @endif

    <form action="{{ route('professors.specializations.store', $professor) }}" method="POST">
        @csrf
        <table class="w-full">
            <thead>
            <tr>
                <td>Course Title</td>
                <td>Course Name</td>
                <td>Select</td>
            </tr>
            </thead>
            <tbody>
            @forelse($courses as $course)
                <tr>
                    <td>{{ $course->course_title }}</td>
                    <td>{{ $course->course_name }}</td>
                    <td>
                        <input type="checkbox" name="courses[]" value="{{ $course->id }}">
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-gray-500">No courses found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </form>
@endsection
