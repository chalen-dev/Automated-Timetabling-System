@extends('app')
@section('title', $timetable->timetable_name . ' - Overview')

@section('content')
    <div class="flex flex-col w-full p-4 pl-39 text-gray-800 justify-center items-center">
        <div class="w-full max-w-[1320px]">
            <div class="flex items-center justify-between mb-3">
                <div class="text-white font-semibold">
                    Timetable Overview (Room Grid)
                </div>

                <div class="text-sm text-gray-500">
                    {{ $timetable->timetable_name }}
                </div>
            </div>

            <style>
                /* Fix “overflow left/right”: force text to stay inside the cell */
                .tt-cell {
                    overflow: hidden;
                    white-space: pre-line;
                    overflow-wrap: anywhere;
                    word-break: break-word;
                }

                /* Keep sticky Time column from being visually overlapped by colored blocks */
                .tt-time-sticky {
                    position: sticky;
                    left: 0;
                    z-index: 30;
                    background: white; /* solid background so colored cells don't show under it */
                }

                /* If a room column is hidden, remove borders/padding so it doesn't leave artifacts */
                .tt-room-col[style*="display: none"] {
                    border: 0 !important;
                    padding: 0 !important;
                }
            </style>

            {{-- TOP BAR: Filter timetable + Filter rooms + Term selector --}}
            <div
                class="relative flex items-center justify-center w-full p-3 gap-6 bg-gray-100 border border-gray-200 rounded-t-lg"
                x-data="{
                    openFilter: false,
                    openRoomFilter: false,
                    openUnplaced: false
                }"
                @click.outside="openFilter = false; openRoomFilter = false"
            >
                <div class="absolute left-3 flex gap-3">
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

                <a
                    href="{{ route('timetables.timetable-overview.index', $timetable) }}?term=0"
                    class="px-6 py-2 font-semibold rounded-lg shadow transition cursor-pointer
                        {{ $termIndex === 0 ? 'bg-red-700 text-white hover:bg-red-800' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}"
                >
                    1st Term
                </a>

                <a
                    href="{{ route('timetables.timetable-overview.index', $timetable) }}?term=1"
                    class="px-6 py-2 font-semibold rounded-lg shadow transition cursor-pointer
                        {{ $termIndex === 1 ? 'bg-red-700 text-white hover:bg-red-800' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}"
                >
                    2nd Term
                </a>

                {{-- TIMETABLE FILTER DROPDOWN --}}
                <div
                    x-show="openFilter"
                    x-cloak
                    x-transition
                    class="absolute top-full left-3 mt-2 z-50 bg-white rounded-md shadow-lg p-4 w-[720px] max-w-[90vw] border"
                >
                    <div class="flex justify-end mb-2">
                        <button
                            type="button"
                            @click="openFilter = false"
                            class="text-gray-500 hover:text-gray-800 text-lg leading-none"
                            aria-label="Close"
                        >
                            &times;
                        </button>
                    </div>

                    {{-- Programs --}}
                    <div class="mb-3">
                        <h4 class="text-xs font-semibold text-gray-600 uppercase mb-2">Programs</h4>
                        <div class="flex flex-wrap gap-2">
                            <button
                                class="tt-program-filter px-3 py-1.5 rounded-full text-xs font-semibold border bg-gray-200 text-gray-800"
                                data-program="all"
                            >
                                All Programs
                            </button>

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
                            @foreach(['all','1st','2nd','3rd','4th'] as $year)
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
                            @foreach(['all','morning','afternoon','evening'] as $time)
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
                    class="absolute top-full left-3 mt-2 z-50 bg-white rounded-md shadow-lg p-4 w-[720px] max-w-[90vw] border"
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

                    <h4 class="text-xs font-semibold text-gray-600 uppercase mb-3">Rooms</h4>

                    @if (empty($roomsByType))
                        <div class="text-sm text-gray-500 italic">
                            No rooms available.
                        </div>
                    @else
                        @foreach ($roomsByType as $type => $roomList)
                            <div class="mb-3" data-room-type="{{ $type }}">
                                <div class="font-semibold text-xs text-gray-700 mb-1">
                                    {{ ucfirst($type) }}
                                </div>

                                <div class="flex flex-wrap gap-3">
                                    @foreach ($roomList as $room)
                                        <label class="cursor-pointer">
                                            <input
                                                type="checkbox"
                                                class="tt-room-filter sr-only peer"
                                                data-room="{{ $room['name'] }}"
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
                    @endif
                </div>

                <div class="absolute right-3">
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

                    <div
                        x-show="openUnplaced"
                        x-cloak
                        @click.outside="openUnplaced = false"
                        class="absolute right-0 top-full mt-2 z-50 bg-white rounded-md shadow-lg p-4 w-[520px] max-w-[90vw] border"
                    >
                        <h2 class="text-lg font-semibold text-gray-800 mb-3">
                            Unplaced Course Sessions
                        </h2>

                        @if (empty($unplacedGroups))
                            <div class="text-sm text-gray-500 italic">
                                No unplaced sessions found.
                            </div>
                        @else
                            <div class="max-h-[60vh] overflow-y-auto pr-2 space-y-3">
                                @foreach ($unplacedGroups as $group)
                                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                                        <div class="flex items-center justify-between bg-gray-100 px-4 py-2">
                                            <div class="font-semibold text-gray-800 text-sm">
                                                {{ $group['group_label'] }}
                                            </div>
                                            <div class="text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-full px-2 py-1">
                                                {{ $group['count'] }} unplaced
                                            </div>
                                        </div>

                                        <div class="divide-y divide-gray-200">
                                            @foreach ($group['items'] as $item)
                                                <div class="px-4 py-2">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="min-w-0">
                                                            <div class="font-semibold text-gray-800 text-[12px] leading-tight">
                                                                {{ $item['course_title'] !== '' ? $item['course_title'] : 'Course Session #' . $item['course_session_id'] }}
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
                            </div>
                        @endif
                    </div>

                </div>

            </div>

            <div class="overflow-x-auto bg-white rounded-b-lg border border-gray-200 border-t-0 shadow-md">
                @if (!empty($error))
                    <div class="p-4 text-sm text-red-700">
                        {{ $error }}
                    </div>
                @elseif (empty($rooms) || empty($timeLabels) || empty($grid))
                    <div class="p-4 text-sm text-gray-500 italic">
                        No overview data found.
                    </div>
                @else
                    @php
                        $dayNames = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

                        /* Fixed widths so days never expand */
                        $dayCellW = 'w-[120px] min-w-[120px] max-w-[120px]';
                        $timeW = 'w-[90px] min-w-[90px] max-w-[90px]';

                        /* Border rules:
                           - Only show thick VERTICAL separators:
                             1) between Time column and first day of each room
                             2) between rooms (right side of Saturday)
                           - No thick borders inside the room, and no thick top/bottom boxes. */
                        $sepColor = 'border-gray-300';
                        $thinColor = 'border-gray-300';
                    @endphp

                    <table class="min-w-full border-collapse table-fixed text-xs md:text-sm">
                        <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                        <tr>
                            <th class="border {{ $thinColor }} px-3 py-2 text-left tt-time-sticky {{ $timeW }}" rowspan="2">
                                Time
                            </th>

                            @foreach ($rooms as $roomName)
                                <th
                                    class="tt-room-col border {{ $thinColor }} px-3 py-2 text-center"
                                    colspan="6"
                                    data-room="{{ $roomName }}"
                                >
                                    {{ $roomName }}
                                </th>
                            @endforeach
                        </tr>

                        <tr>
                            @foreach ($rooms as $roomIndex => $roomName)
                                @foreach ($dayNames as $dayIdx => $day)
                                    @php
                                        $isFirstDay = ($dayIdx === 0);
                                        $isLastDay  = ($dayIdx === 5);

                                        $sepLeft  = $isFirstDay && $roomIndex > 0 ? ('border-l-2 ' . $sepColor) : '';
                                        $sepRight = '';
                                    @endphp
                                    <th
                                        class="tt-room-col border {{ $thinColor }} px-2 py-2 text-center {{ $dayCellW }} {{ $sepLeft }} {{ $sepRight }}"
                                        data-room="{{ $roomName }}"
                                    >
                                        {{ $day }}
                                    </th>
                                @endforeach
                            @endforeach
                        </tr>
                        </thead>

                        <tbody class="text-gray-700">
                        @foreach ($timeLabels as $ti => $timeLabel)
                            <tr class="{{ $ti % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                                <td class="border {{ $thinColor }} px-3 py-2 tt-time-sticky whitespace-nowrap {{ $timeW }}">
                                    {{ $timeLabel }}
                                </td>

                                @foreach ($rooms as $roomName)
                                    @foreach (range(0, 5) as $dayIndex)
                                        @php
                                            $cell = $grid[$ti][$roomName][$dayIndex] ?? null;

                                            $isFirstDay = ($dayIndex === 0);
                                            $isLastDay  = ($dayIndex === 5);

                                            $sepLeft  = $isFirstDay && $roomIndex > 0 ? ('border-l-2 ' . $sepColor) : '';
                                            $sepRight = '';
                                        @endphp

                                        @if (is_array($cell) && array_key_exists('render', $cell) && $cell['render'] === false)
                                            {{-- covered by a rowspan block: render nothing --}}
                                        @elseif (is_array($cell) && array_key_exists('render', $cell) && $cell['render'] === true)
                                            @php
                                                $meta = $cell['meta'] ?? [];
                                                $programId = (string) ($meta['academic_program_id'] ?? 0);
                                                $yearLevel = (string) ($meta['year_level'] ?? '');
                                                $sessionTime = (string) ($meta['session_time'] ?? '');
                                                $rowspan = (int) ($cell['rowspan'] ?? 1);
                                                $text = (string) ($cell['text'] ?? '');
                                                $sessionColor = (string) ($meta['session_color'] ?? '');
                                                $bgStyle = $sessionColor !== '' ? ('background-color: ' . e($sessionColor) . ';') : '';
                                            @endphp

                                            <td
                                                class="tt-room-col tt-cell border {{ $thinColor }} px-1 py-1 align-top {{ $dayCellW }} {{ $sepLeft }} {{ $sepRight }} overview-block"
                                                rowspan="{{ max(1, $rowspan) }}"
                                                data-room="{{ $roomName }}"
                                                data-program="{{ $programId }}"
                                                data-year="{{ strtolower($yearLevel) }}"
                                                data-time="{{ strtolower($sessionTime) }}"
                                                data-bg="{{ $sessionColor }}"
                                                style="{{ $bgStyle }}"
                                            >
                                                <div class="text-[10px] leading-tight font-semibold text-gray-900">
                                                    {{ $text }}
                                                </div>
                                            </td>
                                        @else
                                            <td
                                                class="tt-room-col tt-cell border {{ $thinColor }} px-1 py-1 align-top text-[10px] {{ $dayCellW }} {{ $sepLeft }} {{ $sepRight }}"
                                                data-room="{{ $roomName }}"
                                            >
                                                <span class="text-gray-400 italic">Vacant</span>
                                            </td>
                                        @endif
                                    @endforeach
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    {{-- Timetable filters (program/year/time) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const blocks = Array.from(document.querySelectorAll('.overview-block'));

            let activeProgram = localStorage.getItem('tt_overview_activeProgram') || 'all';
            let activeYear    = localStorage.getItem('tt_overview_activeYear') || 'all';
            let activeTime    = localStorage.getItem('tt_overview_activeTime') || 'all';

            function setActive(btn, selector) {
                document.querySelectorAll(selector).forEach(b => {
                    b.classList.remove('bg-red-700', 'text-white');
                    b.classList.add('bg-gray-200', 'text-gray-800');
                });
                btn.classList.remove('bg-gray-200', 'text-gray-800');
                btn.classList.add('bg-red-700', 'text-white');
            }

            function persist() {
                localStorage.setItem('tt_overview_activeProgram', activeProgram);
                localStorage.setItem('tt_overview_activeYear', activeYear);
                localStorage.setItem('tt_overview_activeTime', activeTime);
            }

            function applyTimetableFilters() {
                blocks.forEach(cell => {
                    const p = cell.dataset.program || '0';
                    const y = (cell.dataset.year || '').toLowerCase();
                    const t = (cell.dataset.time || '').toLowerCase();

                    const match =
                        (activeProgram === 'all' || p === activeProgram) &&
                        (activeYear === 'all' || y === activeYear.toLowerCase()) &&
                        (activeTime === 'all' || t === activeTime.toLowerCase());

                    if (match) {
                        const bg = cell.dataset.bg || '';
                        cell.style.backgroundColor = bg ? bg : '';
                    } else {
                        cell.style.backgroundColor = '#e6e7e9';
                    }
                });
            }

            document.querySelectorAll('.tt-program-filter').forEach(btn => {
                if (btn.dataset.program === activeProgram) setActive(btn, '.tt-program-filter');
                btn.addEventListener('click', function () {
                    activeProgram = btn.dataset.program || 'all';
                    setActive(btn, '.tt-program-filter');
                    persist();
                    applyTimetableFilters();
                });
            });

            document.querySelectorAll('.tt-year-filter').forEach(btn => {
                if (btn.dataset.year === activeYear) setActive(btn, '.tt-year-filter');
                btn.addEventListener('click', function () {
                    activeYear = btn.dataset.year || 'all';
                    setActive(btn, '.tt-year-filter');
                    persist();
                    applyTimetableFilters();
                });
            });

            document.querySelectorAll('.tt-time-filter').forEach(btn => {
                if (btn.dataset.time === activeTime) setActive(btn, '.tt-time-filter');
                btn.addEventListener('click', function () {
                    activeTime = btn.dataset.time || 'all';
                    setActive(btn, '.tt-time-filter');
                    persist();
                    applyTimetableFilters();
                });
            });

            applyTimetableFilters();
        });
    </script>

    {{-- Room filters (localStorage) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roomCheckboxes = document.querySelectorAll('.tt-room-filter');
            const roomCols = document.querySelectorAll('.tt-room-col');

            let activeRooms = new Set();
            const storageKey = 'tt_overview_activeRooms';

            // default = none selected
            const savedRooms = JSON.parse(localStorage.getItem(storageKey) || 'null');
            if (savedRooms && Array.isArray(savedRooms)) {
                savedRooms.forEach(r => activeRooms.add(r));
            }

            function persistRooms() {
                localStorage.setItem(storageKey, JSON.stringify(Array.from(activeRooms)));
            }

            function applyRoomFilter() {
                roomCols.forEach(el => {
                    const room = el.dataset.room;
                    if (!room) return;
                    el.style.display = activeRooms.has(room) ? '' : 'none';
                });
            }

            roomCheckboxes.forEach(cb => {
                const room = cb.dataset.room;
                cb.checked = activeRooms.has(room);

                cb.addEventListener('change', function () {
                    if (!room) return;

                    if (cb.checked) activeRooms.add(room);
                    else activeRooms.delete(room);

                    persistRooms();
                    applyRoomFilter();
                });
            });

            applyRoomFilter();
        });
    </script>
@endsection
