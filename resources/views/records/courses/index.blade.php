@extends('app')

@section('title', 'Courses')

@section('content')
    <div class="w-full p-4">

        <div class="flex justify-between items-center mb-6">
            <div class="flex gap-20 items-center justify-between">
                <h1 class="text-xl font-bold mb-4 text-white">List of Courses</h1>
                <livewire:input.search-bar
                    :action="route('courses.index')"
                    placeholder="Search by course title, course name, course type, or program..."
                />
            </div>
            <a href="{{ route('courses.create') }}"
               class="bg-yellow-500 text-[#5e0b0b] px-4 py-2 rounded-lg font-semibold shadow hover:bg-yellow-600 active:bg-yellow-700 transition-all duration-150">
                Create
            </a>
        </div>

        <table class="w-full text-left border-separate border-spacing-0 bg-white rounded-lg shadow-md overflow-hidden">
            <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
            <tr>
                <th class="px-6 py-3 font-semibold">Course Title</th>
                <th class="px-6 py-3 font-semibold">Course Name</th>
                <th class="px-6 py-3 font-semibold">Course Type</th>
                <th class="px-6 py-3 font-semibold">Duration</th>
                <th class="px-6 py-3 font-semibold">Units</th>
                <th class="px-6 py-3 font-semibold">Academic Programs</th>
                <th class="px-6 py-3 font-semibold text-center">Action</th>
            </tr>
            </thead>
            <tbody class="text-gray-700">
            @forelse($courses as $course)
                <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-3">{{ $course->course_title }}</td>
                    <td class="px-6 py-3">{{ $course->course_name }}</td>
                    <td class="px-6 py-3">{{ $course->course_type }}</td>
                    <td class="px-6 py-3">{{ $course->duration_type }}</td>
                    <td class="px-6 py-3">{{ $course->unit_load }}</td>
                    <td class="px-6 py-3">
                        {{
                            $course->academicPrograms->isNotEmpty()
                            ? $course->academicPrograms
                                ->pluck('program_abbreviation')
                                ->filter()
                                ->implode(', ')
                            : 'No Programs'
                        }}
                    </td>
                    <td class="px-6 py-3 text-center">
                        <div class="flex flex-row gap-2 justify-center items-center">
                            <!-- Set Programs Button (parallel to Rooms' Set Exclusive Programs) -->
                            <a href="{{ route('courses.course-academic-programs.index', $course) }}"
                               class="bg-gray-200 text-gray-800 px-3 py-2 rounded-lg font-semibold shadow hover:bg-gray-300 hover:text-gray-900 active:bg-gray-400 transition-all duration-150">
                                Set Programs
                            </a>

                            <!-- Show Button -->
                            <livewire:buttons.show :route="'courses.show'" :params="$course"/>

                            <!-- Edit Button -->
                            <livewire:buttons.edit :route="'courses.edit'" :params="$course"/>

                            <!-- Delete Button -->
                            <livewire:buttons.delete
                                action="courses.destroy"
                                :params="$course"
                                item_name="course"
                                btnType="icon"
                                class="text-gray-600 px-3 py-2 rounded-lg hover:bg-gray-200 hover:text-gray-800 active:bg-gray-300 transition-all duration-150 flex items-center justify-center w-10 h-10"
                            />
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-6 text-gray-500">
                        No courses found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
