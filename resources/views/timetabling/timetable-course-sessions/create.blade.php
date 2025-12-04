@extends('app')

@section('title', 'Add Sessions')

@section('content')

    <div class="flex justify-between gap-[50px]">
        <h1 class="text-[18px] text-white">Choose Courses for {{ $sessionGroup->session_name }}</h1>

        {{-- Search bar for Courses --}}
        <livewire:input.search-bar :action="route('timetables.session-groups.course-sessions.create', [$timetable, $sessionGroup])">
            {{-- Keep selected courses when searching --}}
            @foreach(old('courses', $selected ?? []) as $selectedCourseId)
                <input type="hidden" name="courses[]" value="{{ $selectedCourseId }}">
            @endforeach
        </livewire:input.search-bar>
    </div>


    <form action="{{ route('timetables.session-groups.course-sessions.store', [$timetable, $sessionGroup]) }}" method="POST">
        @csrf

        <div class="flex gap-2 mb-4">
            <button type="submit" class="bg-yellow-500 text-[#5e0b0b] px-4 py-2 rounded-lg font-semibold shadow hover:bg-yellow-600 active:bg-yellow-700 transition-all duration-150">Add</button>
            <a href="{{ route('timetables.session-groups.index', $timetable) }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold shadow hover:bg-gray-300 hover:text-gray-900 active:bg-gray-400 transition-all duration-150">Back</a>
        </div>

        @if(isset($message))
            <div class="text-red-500 mb-4">{{ $message }}</div>
        @endif

        <table class="w-full border text-left border-separate border-spacing-0 bg-white">
            <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
            <tr>
                <th class="px-6 py-3">Course Title</th>
                <th class="px-6 py-3">Course Name</th>
                <th class="px-6 py-3">Course Type</th>
                <th class="px-6 py-3">Units</th>
                <th class="px-6 py-3">Duration</th>
                <th class="px-6 py-3 text-center">Select</th>
            </tr>
            </thead>
            <tbody class="text-gray-700">
            @foreach($courses as $course)
                <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors cursor-pointer"
                    onclick="if(event.target.type !== 'checkbox') this.querySelector('input[type=checkbox]').click()">
                    <td class="px-6 py-3">{{ $course->course_title }}</td>
                    <td class="px-6 py-3">{{ $course->course_name }}</td>
                    <td class="px-6 py-3">{{ $course->course_type }}</td>
                    <td class="px-6 py-3">{{ $course->unit_load }}</td>
                    <td class="px-6 py-3">{{ $course->duration_type }}</td>
                    <td class="px-6 py-3 text-center">
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
