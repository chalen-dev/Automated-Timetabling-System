@extends('app')

@section('title', 'Delete Course Sessions')

@section('content')
    <div class="w-full pl-39 p-4">
        {{-- Header --}}
        <div class="flex flex-row mb-6 justify-between items-center">
            <div class="flex flex-col text-[#5e0b0b]">
                <h1 class="text-[18px] text-white">
                    {{ $timetable->timetable_name }} —
                    {{ $sessionGroup->academicProgram->program_abbreviation ?? 'Unknown' }}
                    {{ $sessionGroup->session_name }}
                    {{ $sessionGroup->year_level }} Year
                    @if($sessionGroup->session_time)
                        ({{ ucfirst($sessionGroup->session_time) }})
                    @endif
                </h1>
                <p class="text-sm text-gray-300 mt-1">
                    Select one or more course sessions to permanently delete
                </p>
            </div>

            <div class="flex gap-3">
                <a
                    href="{{ route('timetables.session-groups.course-sessions.index', [$timetable, $sessionGroup]) }}"
                    class="bg-white text-[#800000] px-4 py-2 rounded-lg font-semibold border border-[#800000] hover:bg-gray-50"
                >
                    Cancel
                </a>
            </div>
        </div>

        {{-- Warning --}}
        <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
            <strong>Warning:</strong>
            Deleting course sessions will also remove all their placements from the timetable.
            This action <strong>cannot be undone</strong>.
        </div>

        <form
            method="POST"
            action="{{ route('timetables.session-groups.course-sessions.bulk-destroy', [$timetable, $sessionGroup]) }}"
            id="bulk-delete-form"
        >
            @csrf

            {{-- Controls --}}
            <div class="flex items-center justify-between mb-3">
                <div class="flex gap-3">
                    <button
                        type="button"
                        id="select-all"
                        class="px-3 py-1 rounded border border-gray-300 text-sm bg-white hover:bg-gray-100"
                    >
                        Select all
                    </button>

                    <button
                        type="button"
                        id="deselect-all"
                        class="px-3 py-1 rounded border border-gray-300 text-sm bg-white hover:bg-gray-100"
                    >
                        Deselect all
                    </button>
                </div>

                <button
                    type="submit"
                    class="bg-red-700 text-white px-4 py-2 rounded-lg font-semibold shadow hover:bg-red-800 disabled:opacity-50"
                    id="delete-selected"
                    disabled
                >
                    Delete selected
                </button>
            </div>

            {{-- Table --}}
            <div class="bg-white rounded-[12px] shadow-md overflow-hidden">
                <table class="w-full text-left border-separate border-spacing-0">
                    <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 w-10 text-center"></th>
                        <th class="px-6 py-3 font-semibold">Course Title</th>
                        <th class="px-6 py-3 font-semibold">Course Name</th>
                        <th class="px-6 py-3 font-semibold">Units</th>
                        <th class="px-6 py-3 font-semibold">Type</th>
                        <th class="px-6 py-3 font-semibold">Academic Term</th>
                    </tr>
                    </thead>
                    <tbody class="text-gray-700">
                    @forelse($courseSessions as $courseSession)
                        <tr class="border-t border-gray-200 hover:bg-red-50 transition-colors">
                            <td class="px-4 py-3 text-center">
                                <input
                                    type="checkbox"
                                    name="course_sessions[]"
                                    value="{{ $courseSession->id }}"
                                    class="course-session-checkbox w-4 h-4"
                                />
                            </td>
                            <td class="px-6 py-3">
                                {{ $courseSession->course->course_title ?? 'Unknown' }}
                            </td>
                            <td class="px-6 py-3">
                                {{ $courseSession->course->course_name ?? '' }}
                            </td>
                            <td class="px-6 py-3">
                                {{ $courseSession->course->unit_load ?? '' }}
                            </td>
                            <td class="px-6 py-3">
                                {{ $courseSession->course->course_type ?? '' }}
                            </td>
                            <td class="px-6 py-3">
                                {{ $courseSession->academic_term ? ucfirst($courseSession->academic_term) : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-6 text-center text-gray-500">
                                No course sessions found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    </div>

    {{-- Inline JS (kept minimal, no framework assumptions) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = Array.from(document.querySelectorAll('.course-session-checkbox'));
            const deleteBtn = document.getElementById('delete-selected');

            function updateButtonState() {
                deleteBtn.disabled = !checkboxes.some(cb => cb.checked);
            }

            document.getElementById('select-all').addEventListener('click', function () {
                checkboxes.forEach(cb => cb.checked = true);
                updateButtonState();
            });

            document.getElementById('deselect-all').addEventListener('click', function () {
                checkboxes.forEach(cb => cb.checked = false);
                updateButtonState();
            });

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateButtonState);
            });

        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = Array.from(document.querySelectorAll('.course-session-checkbox'));
            const deleteBtn = document.getElementById('delete-selected');
            const form = document.getElementById('bulk-delete-form');

            function updateButtonState() {
                deleteBtn.disabled = !checkboxes.some(cb => cb.checked);
            }

            document.getElementById('select-all').addEventListener('click', function () {
                checkboxes.forEach(cb => cb.checked = true);
                updateButtonState();
            });

            document.getElementById('deselect-all').addEventListener('click', function () {
                checkboxes.forEach(cb => cb.checked = false);
                updateButtonState();
            });

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateButtonState);
            });

            form.addEventListener('submit', function (e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Delete selected courses?',
                    text: 'This will permanently remove the selected course sessions and clear them from the timetable.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#7f1d1d',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, delete them',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>

@endsection
