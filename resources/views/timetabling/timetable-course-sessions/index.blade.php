@extends('app')

@section('title', $timetable->timetable_name . ' · ' . ($sessionGroup->session_name ?? 'Session') . ' · Course Sessions')

@section('content')
    <div class="w-full pl-39 p-4">
        <div class="flex flex-row mb-7 justify-between items-center">
            <div class="flex flex-col text-[#5e0b0b]">
                <h1 class="text-[18px] text-white">
                    {{ $timetable->timetable_name }} —
                    {{ $sessionGroup->academicProgram->program_abbreviation ?? 'Unknown' }}
                    {{ $sessionGroup->session_name }} {{ $sessionGroup->year_level }} Year
                    @if($sessionGroup->session_time)
                        ({{ ucfirst($sessionGroup->session_time) }})
                    @endif
                </h1>
                <p class="text-sm text-gray-300 mt-1">Course sessions for this class session</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('timetables.session-groups.index', $timetable) }}"
                   class="bg-white text-[#800000] px-4 py-2 rounded-lg font-semibold border border-[#800000] hover:bg-gray-50">
                    Back
                </a>

                <a href="{{ route('timetables.session-groups.course-sessions.create', [$timetable, $sessionGroup]) }}"
                   class="bg-[#800000] text-white px-4 py-2 rounded-lg font-semibold">
                    Add Courses
                </a>

                <a href="{{ route('timetables.session-groups.course-sessions.delete', [$timetable, $sessionGroup]) }}"
                   class="bg-white text-red-700 px-4 py-2 rounded-lg font-semibold border border-red-700 hover:bg-red-50">
                    Delete Courses
                </a>
            </div>
        </div>

        <div class="bg-white rounded-[12px] shadow-md overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <form method="GET" class="flex items-center gap-3">
                    <input type="search" name="search" placeholder="Search courses..." value="{{ request('search') }}"
                           class="px-3 py-2 rounded border border-gray-200 w-80" />
                    <button class="px-3 py-2 rounded bg-[#800000] text-white">Search</button>
                </form>
            </div>

            <table class="w-full text-left border-separate border-spacing-0 bg-white">
                <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3 font-semibold">Course Title</th>
                    <th class="px-6 py-3 font-semibold">Course Name</th>
                    <th class="px-6 py-3 font-semibold">Units</th>
                    <th class="px-6 py-3 font-semibold">Type</th>
                    <th class="px-6 py-3 font-semibold">Academic Term</th>
                    <th class="px-6 py-3 font-semibold text-center">Action</th>
                </tr>
                </thead>

                <tbody class="text-gray-700">
                @forelse($courseSessions as $courseSession)
                    <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3">{{ $courseSession->course->course_title ?? 'Unknown Course' }}</td>
                        <td class="px-6 py-3">{{ $courseSession->course->course_name ?? '' }}</td>
                        <td class="px-6 py-3">{{ $courseSession->course->unit_load ?? '' }}</td>
                        <td class="px-6 py-3">{{ $courseSession->course->course_type ?? '' }}</td>
                        <td class="px-6 py-3">
                            <span class="text-sm text-gray-800">
                                {{ $courseSession->academic_term ? ucfirst($courseSession->academic_term) : '—' }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            <livewire:buttons.delete
                                action="timetables.session-groups.course-sessions.destroy"
                                :params="[$timetable, $sessionGroup, $courseSession]"
                                item_name="course session"
                                btnType="icon"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-6 text-center text-gray-500">
                            No course sessions found for this session group.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
