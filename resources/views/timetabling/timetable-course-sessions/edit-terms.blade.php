@extends('app')

@php
    $sessionGroupFullName = trim(sprintf(
        '%s %s %s Year%s',
        $sessionGroup->academicProgram->program_abbreviation ?? 'Unknown',
        $sessionGroup->session_name,
        $sessionGroup->year_level,
        $sessionGroup->session_time ? ' (' . ucfirst($sessionGroup->session_time) . ')' : ''
    ));
@endphp

@section('title', $sessionGroupFullName)

@section('content')
    <div class="w-full pl-39 p-4">
        <div class="flex flex-row mb-7 justify-between items-center">
            <div class="flex flex-col text-[#5e0b0b]">
                <h1 class="text-[18px] text-white">{{ $sessionGroupFullName }}</h1>
                <p class="text-white/80 text-sm">
                    Bulk Edit Academic Terms — {{ $timetable->timetable_name }}
                </p>
            </div>

            <div class="flex gap-3 items-center">
                <a
                    href="{{ route('timetables.session-groups.index', $timetable) }}"
                    class="flex align-center box-border pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-gray-200 text-[#5e0b0b] cursor-pointer shadow-2xl font-[600]"
                >
                    Back
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg px-4 py-3">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-[12px] shadow-md overflow-hidden">
            <div class="pt-4 pb-2 flex flex-row justify-between w-full bg-gray-100">
                <div class="pl-6 flex items-center gap-3">
                    <p class="font-bold">Course Sessions — Edit Academic Terms</p>
                </div>

                <div class="pr-6 text-sm text-gray-600">
                    Change terms below, then click <span class="font-semibold">Confirm Changes</span>.
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('timetables.session-groups.course-sessions.bulk-update-terms', [$timetable, $sessionGroup]) }}"
            >
                @csrf
                @method('PATCH')

                <table class="w-full text-left border-separate border-spacing-0 bg-white">
                    <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Course Title</th>
                        <th class="px-6 py-3 font-semibold">Course Name</th>
                        <th class="px-6 py-3 font-semibold">Units</th>
                        <th class="px-6 py-3 font-semibold">Type</th>
                        <th class="px-6 py-3 font-semibold">Academic Term</th>
                    </tr>
                    </thead>

                    <tbody class="text-gray-700">
                    @php
                        $termOrder = ['1st' => 1, '2nd' => 2, 'semestral' => 3];
                        $sorted = ($courseSessions ?? collect())->sortBy(function ($cs) use ($termOrder) {
                            return $termOrder[$cs->academic_term] ?? 99;
                        });
                    @endphp

                    @forelse($sorted as $courseSession)
                        <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3">{{ $courseSession->course->course_title ?? 'Unknown Course' }}</td>
                            <td class="px-6 py-3">{{ $courseSession->course->course_name ?? '' }}</td>
                            <td class="px-6 py-3">{{ $courseSession->course->unit_load ?? '' }}</td>
                            <td class="px-6 py-3">{{ $courseSession->course->course_type ?? '' }}</td>
                            <td class="px-6 py-3">
                                <select
                                    name="academic_term[{{ $courseSession->id }}]"
                                    data-original="{{ $courseSession->academic_term ?? '' }}"
                                    @if(($courseSession->course->duration_type ?? null) === 'semestral') disabled @endif
                                    class="term-select border border-gray-300 rounded-md text-sm px-2 py-1 focus:ring-2 focus:ring-maroon-600 focus:outline-none"
                                >
                                    @if(($courseSession->course->duration_type ?? null) === 'semestral')
                                        <option value="semestral" selected>semestral</option>
                                    @else
                                        <option value="" {{ is_null($courseSession->academic_term) ? 'selected' : '' }}>-- Select Term --</option>
                                        <option value="1st" {{ $courseSession->academic_term === '1st' ? 'selected' : '' }}>1st</option>
                                        <option value="2nd" {{ $courseSession->academic_term === '2nd' ? 'selected' : '' }}>2nd</option>
                                    @endif
                                </select>

                                @error("academic_term.{$courseSession->id}")
                                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t border-gray-200">
                            <td class="px-6 py-6 text-sm text-gray-600" colspan="5">
                                No course sessions found for this class session.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="px-6 py-4 border-t border-gray-200 bg-white flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        <span id="changedCount" class="font-semibold">0</span> change(s) selected
                    </div>

                    <button
                        type="submit"
                        class="bg-[#800000] text-white px-4 py-2 rounded-lg font-semibold shadow hover:bg-[#660000] active:bg-[#4d0000] transition-all duration-150"
                    >
                        Confirm Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
<style>
    /* Stronger than hover utility classes so multiple changed rows stay highlighted */
    tr.sg-term-changed td {
        background-color: rgb(254 252 232); /* close to Tailwind bg-yellow-50 */
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selects = Array.from(document.querySelectorAll('.term-select'));
        const changedCountEl = document.getElementById('changedCount');

        function recomputeChanged() {
            let changed = 0;

            selects.forEach(function (sel) {
                const original = (sel.getAttribute('data-original') || '');
                const current = (sel.value || '');
                const row = sel.closest('tr');

                const isChanged = original !== current;
                if (isChanged) changed++;

                if (row) {
                    row.classList.toggle('sg-term-changed', isChanged);
                }
            });

            if (changedCountEl) {
                changedCountEl.textContent = String(changed);
            }
        }

        selects.forEach(function (sel) {
            sel.addEventListener('change', recomputeChanged);
        });

        recomputeChanged();
    });
</script>
