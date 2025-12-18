@extends('app')
@section('title', $timetable->timetable_name)

@section('content')
    <div class="flex flex-col w-full p-4 pl-39 text-gray-800 justify-center items-center">

        @php
            $activeTermIndex = $sheetIndex < 6 ? 0 : 1;
            $activeDayIndex  = $sheetIndex % 6;
        @endphp


        @if (!empty($tableData) && isset($tableData[0]))

            <div
                class="relative w-full bg-gray-100 border-b border-gray-200 rounded-t-lg px-4 py-3"
                x-data="{ openFilter: false, openRoomFilter: false, openUnplaced: false }"
            >
                <div class="grid grid-cols-3 items-center">

                    {{-- LEFT: FILTER BUTTONS --}}
                    <div class="flex gap-3">
                        {{-- Filter timetable --}}
                        <button
                            type="button"
                            @click.stop="openFilter = !openFilter; openRoomFilter = false"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border shadow-sm bg-white text-gray-800 hover:bg-gray-50"
                        >
                            <i class="bi bi-funnel-fill"></i>
                            <span class="font-semibold">Filter timetable</span>
                        </button>

                        {{-- Filter rooms --}}
                        <button
                            type="button"
                            @click.stop="openRoomFilter = !openRoomFilter; openFilter = false"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border shadow-sm bg-white text-gray-800 hover:bg-gray-50"
                        >
                            <i class="bi bi-building"></i>
                            <span class="font-semibold">Filter rooms</span>
                        </button>
                    </div>

                    {{-- CENTER: TERM BUTTONS --}}
                    <div class="flex justify-center gap-6">
                        <button
                            type="button"
                            class="view-term-button px-6 py-2 font-semibold rounded-lg shadow
                             {{ $activeTermIndex === 0 ? 'bg-red-700 text-white' : 'bg-gray-200 text-gray-700' }}"
                            data-term-index="0"
                        >
                            1st Term
                        </button>

                        <button
                            type="button"
                            class="view-term-button px-6 py-2 font-semibold rounded-lg shadow
                            {{ $activeTermIndex === 1 ? 'bg-red-700 text-white' : 'bg-gray-200 text-gray-700' }}"
                            data-term-index="1"
                        >
                            2nd Term
                        </button>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="button"
                            @click.stop="openUnplaced = !openUnplaced"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border shadow-sm bg-white text-gray-800 hover:bg-gray-50"
                        >
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <span class="font-semibold">Unplaced</span>

                            @if (!empty($unplacedGroups))
                                <span class="text-xs text-gray-600">
                                    ({{ collect($unplacedGroups)->sum('count') }})
                                </span>
                            @endif
                        </button>
                    </div>
                </div>

                {{-- TIMETABLE FILTER DROPDOWN --}}
                <div
                    x-show="openFilter"
                    x-cloak
                    x-transition
                    class="absolute top-full left-4 mt-2 z-50 bg-white rounded-md shadow-lg p-4 w-[720px] max-w-[90vw] border"
                >
                    <div class="flex justify-end mb-2">
                        <button
                            type="button"
                            @click="openFilter = false"
                            aria-label="Close"
                            class="
                                w-8 h-8
                                flex items-center justify-center
                                rounded-full
                                text-gray-500
                                hover:text-gray-800
                                hover:bg-gray-200
                                transition
                            "
                        >
                            &times;
                        </button>
                    </div>

                    {{-- Programs --}}
                    <div class="mb-3">
                        <h4 class="text-xs font-semibold text-gray-600 uppercase mb-2">Programs</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($sessionGroupsByProgram as $programId => $groups)
                                <button
                                    class="tt-program-filter px-3 py-1.5 rounded-full text-xs font-semibold border bg-gray-200 text-gray-800"
                                    data-program="{{ $programId }}"
                                >
                                    {{ $groups->first()->academicProgram->program_abbreviation ?? 'UNK' }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Year --}}
                    <div class="mb-3">
                        <h4 class="text-xs font-semibold text-gray-600 uppercase mb-2">Year Level</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['1st','2nd','3rd','4th'] as $year)
                                <button
                                    class="tt-year-filter px-3 py-1.5 rounded-full text-xs font-semibold border bg-gray-200 text-gray-800"
                                    data-year="{{ $year }}"
                                >
                                    {{ ucfirst($year) }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Time --}}
                    <div>
                        <h4 class="text-xs font-semibold text-gray-600 uppercase mb-2">Time of Day</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['morning','afternoon','evening'] as $time)
                                <button
                                    class="tt-time-filter px-3 py-1.5 rounded-full text-xs font-semibold border bg-gray-200 text-gray-800"
                                    data-time="{{ $time }}"
                                >
                                    {{ ucfirst($time) }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ROOM FILTER DROPDOWN --}}
                <div
                    x-show="openRoomFilter"
                    x-cloak
                    x-transition
                    class="absolute top-full left-4 mt-2 z-50 bg-white rounded-md shadow-lg p-4 w-[720px] max-w-[90vw] border"
                >
                    <div class="flex justify-end mb-2">
                        <button
                            type="button"
                            @click="openRoomFilter = false"
                            class="text-gray-500 hover:text-gray-800 text-lg leading-none"
                            aria-label="Close"
                        >
                            &times;
                        </button>
                    </div>

                    <h4 class="text-xs font-semibold text-gray-600 uppercase mb-3">
                        Rooms
                    </h4>

                    @php
                        $roomNames = collect($tableData[0])
                            ->filter(fn ($v, $i) => $i > 0 && trim($v) !== '')
                            ->values();

                        $roomsByType = $roomNames->map(function ($name) {
                            $room = \App\Models\Records\Room::where('room_name', $name)->first();
                            return [
                                'name' => $name,
                                'type' => $room?->room_type ?? 'Unknown',
                            ];
                        })->groupBy('type');
                    @endphp

                    @foreach($roomsByType as $type => $rooms)
                        <div class="mb-3" data-room-type="{{ $type }}">
                            <div class="font-semibold text-xs text-gray-700 mb-1">
                                {{ ucfirst($type) }}
                            </div>

                            <div class="flex flex-wrap gap-3">
                                @foreach($rooms as $room)
                                    <label class="cursor-pointer">
                                        <input
                                            type="checkbox"
                                            class="tt-room-filter sr-only peer"
                                            data-room="{{ $room['name'] }}"
                                            checked
                                        >
                                        <span
                                            class="
                                                inline-flex items-center justify-center
                                                px-3 py-1.5
                                                rounded-full
                                                text-xs font-semibold
                                                border
                                                bg-gray-200 text-gray-800
                                                hover:bg-gray-300
                                                peer-checked:bg-red-700
                                                peer-checked:text-white
                                                peer-checked:border-red-700
                                                transition
                                            "
                                        >
                                            {{ $room['name'] }}
                                        </span>
                                    </label>

                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- RIGHT-SIDE UNPLACED PANEL (MATCHES OVERVIEW) --}}
                <div
                    x-show="openUnplaced"
                    x-cloak
                    x-transition
                    @click.outside="openUnplaced = false"
                    class="fixed top-[140px] right-4 z-[999] bg-white rounded-xl shadow-xl
                    w-[520px] max-w-[90vw] h-[calc(100vh-180px)] border flex flex-col"
                >
                    <div class="flex items-center justify-between px-5 py-4 border-b">
                        <h2 class="text-lg font-semibold text-gray-800">
                            Unplaced Course Sessions
                        </h2>

                        <button
                            type="button"
                            @click="openUnplaced = false"
                            class="text-gray-500 hover:text-gray-800 text-xl leading-none"
                            aria-label="Close"
                        >
                            &times;
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4 space-y-3">
                        @if (empty($unplacedGroups))
                            <div class="text-sm text-gray-500 italic">
                                No unplaced sessions found.
                            </div>
                        @else
                            @foreach ($unplacedGroups as $group)
                                <div class="border border-gray-200 rounded-lg overflow-hidden">
                                    @php
                                        $headerBg = $group['group_color'] ?? null;
                                    @endphp

                                    <div
                                        class="flex items-center justify-between px-4 py-2"
                                        @if($headerBg)
                                            style="background-color: {{ $headerBg }}; color: #000000;"
                                        @else
                                            class="bg-gray-100"
                                        @endif
                                    >
                                        <div class="font-semibold text-sm">
                                            {{ $group['group_label'] }}
                                        </div>

                                        <div
                                            class="text-xs font-semibold rounded-full px-2 py-1"
                                            style="
                                                background: rgba(255,255,255,0.9);
                                                color: #374151;
                                            "
                                        >
                                            {{ $group['count'] }} unplaced
                                        </div>
                                    </div>


                                    <div class="divide-y divide-gray-200">
                                        @foreach ($group['items'] as $item)
                                            <div class="px-4 py-2">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <div class="font-semibold text-gray-800 text-[12px] leading-tight">
                                                            @if (!empty($item['course_title']))
                                                                {{ $item['course_title'] }}
                                                            @endif
                                                        </div>

                                                        <div class="text-[11px] text-gray-700 leading-tight mt-0.5">
                                                            <span class="font-semibold">Issue:</span>
                                                            {{ $item['reason_title'] }}
                                                        </div>

                                                        @if (!empty($item['reason_hint']))
                                                            <div class="text-[11px] text-gray-500 leading-tight">
                                                                {{ $item['reason_hint'] }}
                                                            </div>
                                                        @endif

                                                        <div class="text-[10px] text-gray-500 leading-tight mt-0.5">
                                                            <span class="font-semibold">Term tried:</span>
                                                            {{ $item['terms_tried'] }}
                                                        </div>
                                                    </div>

                                                    <div class="text-[10px] text-gray-400 whitespace-nowrap">
                                                        #{{ $item['course_session_id'] }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>



            </div>



            <div class="grid grid-cols-6 w-full bg-white p-3 gap-2 border-t border-gray-200">
                @foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $index => $day)
                    <button
                        type="button"
                        class="view-day-button w-full py-2 rounded-lg text-sm font-medium shadow-md transition-all
                {{ $activeDayIndex === $index ? 'bg-red-700 text-white' : 'bg-gray-200 text-gray-700 hover:bg-red-700 hover:text-white' }}"
                        data-day-index="{{ $index }}"
                    >
                        {{ $day }}
                    </button>
                @endforeach
            </div>


            {{-- Table attaches directly to selector (shared border, no gap) --}}
            <div class="overflow-x-auto bg-white rounded-b-lg border border-gray-200 border-t-0 shadow-md min-w-full">
                <table class="min-w-full border-collapse table-auto text-xs md:text-sm">
                    <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                    <tr>
                        @foreach ($tableData[0] as $colIndex => $cell)
                            @if ($colIndex === 0)
                                <th class="border border-gray-200 px-3 py-2 text-center"></th>
                            @else
                                @php
                                    $roomName = $cell;
                                    $roomType = null;

                                    if ($colIndex > 0) {
                                        $room = \App\Models\Records\Room::where('room_name', $cell)->first();
                                        $roomType = $room?->room_type ?? 'Unknown';
                                    }
                                @endphp

                                <th
                                    class="timetable-room-header border border-gray-200 px-3 py-2 text-center"
                                    data-room="{{ $roomName }}"
                                    data-room-type="{{ $roomType }}"
                                >
                                    {{ $cell }}
                                </th>

                            @endif
                        @endforeach
                    </tr>
                    </thead>

                    <tbody class="text-gray-700">

                    @php $cellColors = []; @endphp

                    @foreach ($tableData as $rowIndex => $row)
                        @if ($rowIndex === 0) @continue @endif

                        <tr class="{{ $rowIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-gray-100 transition-colors">

                            @foreach ($row as $colIndex => $cell)

                                @php
                                    $span = $rowspanData[$colIndex][$rowIndex] ?? 1;
                                @endphp

                                @if ($colIndex === 0)
                                    {{-- Time column --}}
                                    <td class="border border-gray-200 px-3 py-2 text-center text-sm">
                                        {{ $cell }}
                                    </td>

                                @else

                                    @php
                                        $cellColor   = $cellColors[$rowIndex][$colIndex] ?? null;
                                        $displayName = $cell;
                                        $courseTitle = '';

                                        if ($span > 0 && strtolower(trim($cell)) !== 'vacant') {

                                            $parts = explode('_', $cell);

                                            if (count($parts) === 4) {
                                                [$programAbbr, $sessionName, $sessionGroupId, $courseSessionId] = $parts;

                                                $sessionGroup  = \App\Models\Timetabling\SessionGroup::find($sessionGroupId);
                                                $courseSession = \App\Models\Timetabling\CourseSession::find($courseSessionId);

                                                if ($sessionGroup) {
                                                    $displayName =
                                                        ($sessionGroup->academicProgram->program_abbreviation ?? $programAbbr) . ' ' .
                                                        $sessionGroup->session_name . ' ' .
                                                        $sessionGroup->year_level . ' Year';

                                                    // NEW: append session_time like in editor (Morning/Afternoon/Evening)
                                                    if (!empty($sessionGroup->session_time)) {
                                                        $prettyTime = ucfirst((string) $sessionGroup->session_time);
                                                        $displayName .= ' (' . $prettyTime . ')';
                                                    }

                                                    if (!empty($sessionGroup->session_color)) {
                                                        $cellColor = $sessionGroup->session_color;
                                                    }
                                                }

                                                if ($courseSession && $courseSession->course) {
                                                    $courseTitle = $courseSession->course->course_title ?? '';
                                                }
                                            }

                                            if ($cellColor === null) {
                                                $cellColor = '#ffffff'; // default white
                                            }

                                            for ($r = $rowIndex; $r < $rowIndex + $span; $r++) {
                                                $cellColors[$r][$colIndex] = $cellColor;
                                            }
                                        }
                                    @endphp

                                    @if ($span > 0)
                                        @php
                                            $cellProgramId = $sessionGroup->academic_program_id ?? '';
                                            $cellYear      = $sessionGroup->year_level ?? '';
                                            $cellTime      = $sessionGroup->session_time ?? '';
                                            $bgColor       = $cellColor ?? '';
                                        @endphp

                                        <td
                                            class="timetable-cell border border-gray-200 px-2 py-2 text-center text-[11px] leading-tight"
                                            rowspan="{{ $span }}"
                                            data-room="{{ $tableData[0][$colIndex] ?? '' }}"

                                            {{-- FILTER METADATA --}}
                                            data-program="{{ $cellProgramId }}"
                                            data-year="{{ $cellYear }}"
                                            data-time="{{ $cellTime }}"
                                            data-bg="{{ $bgColor }}"

                                            {{-- ORIGINAL CONTENT (for restore) --}}
                                            data-original-display="{{ e($displayName) }}"
                                            data-original-title="{{ e($courseTitle) }}"

                                            @if($bgColor) style="background-color: {{ $bgColor }};" @endif
                                        >
                                            @if (strtolower(trim($cell)) === 'vacant')
                                                <span class="text-gray-400 italic text-[11px]">Vacant</span>
                                            @else
                                                <div class="cell-original">
                                                    <div class="font-semibold text-[11px] leading-tight">
                                                        {{ $displayName }}
                                                    </div>

                                                    @if (!empty($courseTitle))
                                                        <div class="italic mt-0.5 text-[10px] leading-tight">
                                                            {{ $courseTitle }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                    @endif


                                @endif

                            @endforeach
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>
        @else
            <livewire:text.empty-table message="No timetable data found."  />
        @endif
    </div>

    {{-- Floating Edit Timetable button (bottom-right, circular with tooltip) --}}
    @can('editTimetable', $timetable)
        @if ($isNotEmpty)
            <a href="{{ route('timetables.timetable-editing-pane.editor', ['timetable' => $timetable->id]) }}"
               class="fixed bottom-6 right-6 z-50 group">
                <div
                    class="flex items-center justify-center w-14 h-14 rounded-full bg-green-500 text-white shadow-xl
                           hover:bg-green-600 transition duration-150 cursor-pointer">
                    <i class="bi bi-pencil-square text-2xl"></i>
                </div>

                {{-- Tooltip --}}
                <div
                    class="absolute right-16 bottom-1/2 translate-y-1/2 opacity-0 pointer-events-none
                           group-hover:opacity-100 group-hover:pointer-events-auto
                           transition-opacity duration-150">
                    <div class="px-3 py-1 rounded-md bg-gray-900 text-white text-xs shadow-lg whitespace-nowrap">
                        Edit timetable
                    </div>
                </div>
            </a>
        @endif
    @endcan

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const baseUrl = @json(route('timetables.timetable-editing-pane.index', ['timetable' => $timetable->id]));
            const totalSheets = {{ (int) $totalSheets }};
            let activeTermIndex = {{ (int) $activeTermIndex }};
            let activeDayIndex  = {{ (int) $activeDayIndex }};

            function goToView(termIndex, dayIndex) {
                const sheetIndex = termIndex * 6 + dayIndex;
                if (sheetIndex < 0 || sheetIndex >= totalSheets || sheetIndex >= 12) return;
                const url = baseUrl + '?sheet=' + sheetIndex;
                window.location.href = url;
            }

            // Replace previous view-day-button binding with this:
            document.querySelectorAll('.view-day-button').forEach(function (btn) {
                btn.addEventListener('click', function (ev) {
                    ev.preventDefault();

                    const idx = parseInt(btn.getAttribute('data-day-index'), 10);
                    if (isNaN(idx) || idx === activeDayIndex) return;

                    // Immediately show feedback locally (toggle styles)
                    document.querySelectorAll('.view-day-button').forEach(b => {
                        b.classList.remove('bg-red-700', 'text-white');
                        b.classList.add('bg-gray-200', 'text-gray-700');
                    });
                    btn.classList.remove('bg-gray-200', 'text-gray-700');
                    btn.classList.add('bg-red-700', 'text-white');

                    // Give the browser a brief tick to paint the change, then navigate
                    // 40-80ms is enough; low enough to feel instant.
                    setTimeout(() => {
                        activeDayIndex = idx;
                        goToView(activeTermIndex, activeDayIndex);
                    }, 60);
                });
            });

            // TERM BUTTON HANDLER (FIX)
            document.querySelectorAll('.view-term-button').forEach(function (btn) {
                btn.addEventListener('click', function (ev) {
                    ev.preventDefault();

                    const termIdx = parseInt(btn.getAttribute('data-term-index'), 10);
                    if (isNaN(termIdx) || termIdx === activeTermIndex) return;

                    // Update term button styles immediately
                    document.querySelectorAll('.view-term-button').forEach(b => {
                        b.classList.remove('bg-red-700', 'text-white');
                        b.classList.add('bg-gray-200', 'text-gray-700');
                    });

                    btn.classList.remove('bg-gray-200', 'text-gray-700');
                    btn.classList.add('bg-red-700', 'text-white');

                    // Preserve current day when switching term
                    setTimeout(() => {
                        activeTermIndex = termIdx;
                        goToView(activeTermIndex, activeDayIndex);
                    }, 60);
                });
            });

            // Optional keyboard navigation (left/right) across sheets
            document.addEventListener('keydown', function (e) {
                const currentSheet = activeTermIndex * 6 + activeDayIndex;

                if (e.key === 'ArrowLeft') {
                    const prev = currentSheet - 1;
                    if (prev >= 0) {
                        const term = prev < 6 ? 0 : 1;
                        const day  = prev % 6;
                        goToView(term, day);
                    }
                } else if (e.key === 'ArrowRight') {
                    const next = currentSheet + 1;
                    if (next < totalSheets && next < 12) {
                        const term = next < 6 ? 0 : 1;
                        const day  = next % 6;
                        goToView(term, day);
                    }
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cells = Array.from(document.querySelectorAll('.timetable-cell'));

            let selectedPrograms = new Set(
                JSON.parse(localStorage.getItem('tt_editor_programs') || '[]')
            );
            let selectedYears = new Set(
                JSON.parse(localStorage.getItem('tt_editor_years') || '[]')
            );
            let selectedTimes = new Set(
                JSON.parse(localStorage.getItem('tt_editor_times') || '[]')
            );

            function persist() {
                localStorage.setItem('tt_editor_programs', JSON.stringify([...selectedPrograms]));
                localStorage.setItem('tt_editor_years', JSON.stringify([...selectedYears]));
                localStorage.setItem('tt_editor_times', JSON.stringify([...selectedTimes]));
            }

            function toggle(btn, set, value) {
                if (set.has(value)) {
                    set.delete(value);
                    btn.classList.remove('bg-red-700', 'text-white');
                    btn.classList.add('bg-gray-200', 'text-gray-800');
                } else {
                    set.add(value);
                    btn.classList.add('bg-red-700', 'text-white');
                    btn.classList.remove('bg-gray-200', 'text-gray-800');
                }
            }

            function applyFilters() {
                cells.forEach(cell => {
                    const p = cell.dataset.program || '';
                    const y = (cell.dataset.year || '').toLowerCase();
                    const t = (cell.dataset.time || '').toLowerCase();

                    const programMatch =
                        selectedPrograms.size === 0 || selectedPrograms.has(p);

                    const yearMatch =
                        selectedYears.size === 0 || selectedYears.has(y);

                    const timeMatch =
                        selectedTimes.size === 0 || selectedTimes.has(t);

                    const visible = programMatch && yearMatch && timeMatch;

                    const originalDisplay = (cell.dataset.originalDisplay || '').trim();
                    const originalTitle   = (cell.dataset.originalTitle || '').trim();

                    // Vacant cells are never filtered
                    if (originalDisplay.toLowerCase() === 'vacant') {
                        cell.style.backgroundColor = '';
                        cell.innerHTML = `<span class="text-gray-400 italic text-[11px]">Vacant</span>`;
                        return;
                    }

                    if (visible) {
                        cell.style.backgroundColor = cell.dataset.bg || '';
                        cell.innerHTML = `
                    <div class="font-semibold text-[11px] leading-tight">
                        ${originalDisplay}
                    </div>
                    ${originalTitle
                            ? `<div class="italic mt-0.5 text-[10px] leading-tight">${originalTitle}</div>`
                            : ''
                        }
                `;
                    } else {
                        cell.style.backgroundColor = '#e6e7e9';
                        cell.innerHTML = `
                    <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">
                        Occupied
                    </div>
                `;
                    }
                });
            }

            // ---------- PROGRAM FILTERS ----------
            document.querySelectorAll('.tt-program-filter').forEach(btn => {
                const val = btn.dataset.program;
                if (selectedPrograms.has(val)) {
                    btn.classList.add('bg-red-700', 'text-white');
                    btn.classList.remove('bg-gray-200', 'text-gray-800');
                }

                btn.addEventListener('click', () => {
                    toggle(btn, selectedPrograms, val);
                    persist();
                    applyFilters();
                });
            });

            // ---------- YEAR FILTERS ----------
            document.querySelectorAll('.tt-year-filter').forEach(btn => {
                const val = btn.dataset.year.toLowerCase();
                if (selectedYears.has(val)) {
                    btn.classList.add('bg-red-700', 'text-white');
                    btn.classList.remove('bg-gray-200', 'text-gray-800');
                }

                btn.addEventListener('click', () => {
                    toggle(btn, selectedYears, val);
                    persist();
                    applyFilters();
                });
            });

            // ---------- TIME FILTERS ----------
            document.querySelectorAll('.tt-time-filter').forEach(btn => {
                const val = btn.dataset.time.toLowerCase();
                if (selectedTimes.has(val)) {
                    btn.classList.add('bg-red-700', 'text-white');
                    btn.classList.remove('bg-gray-200', 'text-gray-800');
                }

                btn.addEventListener('click', () => {
                    toggle(btn, selectedTimes, val);
                    persist();
                    applyFilters();
                });
            });

            applyFilters();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roomCheckboxes = document.querySelectorAll('.tt-room-filter');
            const roomHeaders    = document.querySelectorAll('.timetable-room-header');
            const roomCells      = document.querySelectorAll('.timetable-cell');

            let activeRooms = new Set();

            // Restore from localStorage
            const savedRooms = JSON.parse(localStorage.getItem('tt_activeRooms') || 'null');
            if (savedRooms && Array.isArray(savedRooms)) {
                savedRooms.forEach(r => activeRooms.add(r));
            }

            // Default = all currently rendered rooms
            if (activeRooms.size === 0) {
                roomHeaders.forEach(th => {
                    if (th.dataset.room) {
                        activeRooms.add(th.dataset.room);
                    }
                });
            }

            function applyRoomFilter() {
                // Headers
                roomHeaders.forEach(th => {
                    const room = th.dataset.room;
                    th.style.display = activeRooms.has(room) ? '' : 'none';
                });

                // Cells
                roomCells.forEach(td => {
                    const room = td.dataset.room;
                    td.style.display = activeRooms.has(room) ? '' : 'none';
                });
            }

            function persistRooms() {
                localStorage.setItem(
                    'tt_activeRooms',
                    JSON.stringify(Array.from(activeRooms))
                );
            }

            roomCheckboxes.forEach(cb => {
                // restore checkbox state
                cb.checked = activeRooms.has(cb.dataset.room);

                cb.addEventListener('change', function () {
                    const room = cb.dataset.room;

                    if (cb.checked) {
                        activeRooms.add(room);
                    } else {
                        activeRooms.delete(room);
                    }

                    persistRooms();
                    applyRoomFilter();
                    syncRoomTypeCheckboxes();
                });
            });



            applyRoomFilter();
        });
    </script>




@endsection

