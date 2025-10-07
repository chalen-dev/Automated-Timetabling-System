@extends('app')

@section('title', 'Create Specialization')

@section('content')
    <div class="w-full p-4">

        <!-- Header: Title + Search + Buttons -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex gap-20 items-center justify-between">
                <h1 class="text-xl font-bold mb-0 text-white">
                    Assign Courses to {{ $professor->last_name }}, {{ $professor->first_name }}
                </h1>
                <x-search-bar.search-bar
                    :action="route('professors.specializations.create', $professor)"
                    placeholder="Search courses by title or name..."
                    name="search"
                />
            </div>
            <div class="flex gap-4">
                <!-- Add button: yellow -->
                <button type="submit" form="specializationsForm"
                        class="bg-yellow-500 text-[#5e0b0b] px-4 py-2 rounded-lg font-semibold shadow hover:bg-yellow-600 active:bg-yellow-700 transition-all duration-150">
                    Add
                </button>

                <!-- Back button: gray -->
                <a href="{{ route('professors.specializations.index', $professor) }}"
                   class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold shadow hover:bg-gray-300 hover:text-gray-900 active:bg-gray-400 transition-all duration-150">
                    Back
                </a>
            </div>
        </div>

        @if(isset($message))
            <div class="text-red-500 mb-4">{{ $message }}</div>
        @endif

        <!-- Courses Table Form -->
        <form id="specializationsForm" action="{{ route('professors.specializations.store', $professor) }}" method="POST">
            @csrf
            <table class="w-full text-left border-separate border-spacing-0 bg-white rounded-lg shadow-md overflow-hidden">
                <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3 font-semibold">Course Title</th>
                    <th class="px-6 py-3 font-semibold">Course Name</th>
                    <th class="px-6 py-3 font-semibold text-center">Select</th>
                </tr>
                </thead>
                <tbody class="text-gray-700">
                @forelse($courses as $course)
                    <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors cursor-pointer"
                        onclick="if(event.target.type !== 'checkbox') this.querySelector('input[type=checkbox]').click()">
                        <td class="px-6 py-3">{{ $course->course_title }}</td>
                        <td class="px-6 py-3">{{ $course->course_name }}</td>
                        <td class="px-6 py-3 text-center">
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

    </div>
@endsection
