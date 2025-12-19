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
                                                data-room-type="{{ $type }}"
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
                    <a
                        href="{{ route('timetables.timetable-overview.export', [$timetable, 'term' => $termIndex]) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border shadow-sm bg-white text-gray-800 hover:bg-gray-50 mr-2"
                    >
                        <i class="bi bi-download"></i>
                        <span class="font-semibold">Download</span>
                    </a>
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
                                    <div class="unplaced-group border border-gray-200 rounded-lg overflow-hidden">
                                        @php
                                            $groupBg = !empty($group['group_color'])
                                                ? 'background-color: ' . e($group['group_color']) . ';'
                                                : '';
                                        @endphp

                                        <div
                                            class="flex items-center justify-between px-4 py-2"
                                            style="{{ $groupBg }}"
                                        >
                                            <div class="font-semibold text-gray-800 text-sm">
                                                {{ $group['group_label'] }}
                                            </div>
                                            <div class="unplaced-badge text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-full px-2 py-1">
                                                {{ $group['count'] }}
                                            </div>
                                        </div>

                                        <div class="divide-y divide-gray-200">
                                            @foreach ($group['items'] as $item)
                                                <div
                                                    class="px-4 py-2 unplaced-item"
                                                    data-course-session-id="{{ $item['course_session_id'] }}"
                                                    data-total-laboratory-days="{{ $item['course_total_laboratory_class_days'] ?? 0 }}"
                                                    data-total-lecture-days="{{ $item['course_total_lecture_class_days'] ?? 0 }}"
                                                >
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

                                                {{-- FILTER METADATA --}}
                                                data-program="{{ $programId }}"
                                                data-year="{{ strtolower($yearLevel) }}"
                                                data-time="{{ strtolower($sessionTime) }}"
                                                data-bg="{{ $sessionColor }}"

                                                {{-- ORIGINAL CONTENT (for restore) --}}
                                                data-original-html="{{ e($text) }}"

                                                style="{{ $bgStyle }}"
                                            >
                                                <div class="cell-original text-[10px] leading-tight font-semibold text-gray-900">
                                                    {!! nl2br(e($text)) !!}
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

            let selectedPrograms = new Set(
                JSON.parse(localStorage.getItem('tt_overview_programs') || '[]')
            );
            let selectedYears = new Set(
                JSON.parse(localStorage.getItem('tt_overview_years') || '[]')
            );
            let selectedTimes = new Set(
                JSON.parse(localStorage.getItem('tt_overview_times') || '[]')
            );

            function persist() {
                localStorage.setItem('tt_overview_programs', JSON.stringify([...selectedPrograms]));
                localStorage.setItem('tt_overview_years', JSON.stringify([...selectedYears]));
                localStorage.setItem('tt_overview_times', JSON.stringify([...selectedTimes]));
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
                blocks.forEach(cell => {
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
                    const original = (cell.dataset.originalHtml || '').trim();

                    if (!original) return;

                    if (visible) {
                        cell.style.backgroundColor = cell.dataset.bg || '';
                        cell.innerHTML = `
                    <div class="cell-original text-[10px] leading-tight font-semibold text-gray-900">
                        ${original.replace(/\n/g, '<br>')}
                    </div>
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


    {{-- Room filters (localStorage) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roomCheckboxes = Array.from(document.querySelectorAll('.tt-room-filter'));
            const roomCols = Array.from(document.querySelectorAll('.tt-room-col'));
            const STORAGE_KEY = 'tt_overview_activeRooms';

            // ---------- LOAD SAVED STATE ----------
            let saved = localStorage.getItem(STORAGE_KEY);
            let activeRooms = null;

            try {
                activeRooms = saved ? new Set(JSON.parse(saved)) : null;
            } catch {
                activeRooms = null;
            }

            // ---------- SYNC CHECKBOXES ----------
            roomCheckboxes.forEach(cb => {
                if (activeRooms === null) {
                    // no saved state = initial load → checked visually, but not committed
                    cb.checked = true;
                } else {
                    cb.checked = activeRooms.has(cb.dataset.room);
                }
            });

            // ---------- APPLY ROOM VISIBILITY ----------
            function applyRoomColumns() {
                // no saved state → show all
                if (activeRooms === null) {
                    roomCols.forEach(col => col.style.display = '');
                    return;
                }

                // saved state exists
                roomCols.forEach(col => {
                    col.style.display = activeRooms.has(col.dataset.room) ? '' : 'none';
                });
            }

            // ---------- APPLY UNPLACED FILTER ----------
            function applyUnplacedFilter() {
                const selectedTypes = new Set(
                    roomCheckboxes
                        .filter(cb => cb.checked)
                        .map(cb => cb.dataset.roomType?.toLowerCase())
                        .filter(Boolean)
                );

                const showAll = selectedTypes.size === 0;
                const groups = document.querySelectorAll('.unplaced-group');

                groups.forEach(group => {
                    const items = group.querySelectorAll('.unplaced-item');
                    let visibleCount = 0;

                    items.forEach(item => {
                        if (showAll) {
                            item.style.display = '';
                            visibleCount++;
                            return;
                        }

                        const lab = parseInt(item.dataset.totalLaboratoryDays || '0', 10);
                        const lec = parseInt(item.dataset.totalLectureDays || '0', 10);

                        let match = false;
                        if (selectedTypes.has('comlab') && lab > 0) match = true;
                        if (selectedTypes.has('lecture') && lec > 0) match = true;

                        item.style.display = match ? '' : 'none';
                        if (match) visibleCount++;
                    });

                    const badge = group.querySelector('.unplaced-badge');
                    if (badge) badge.textContent = visibleCount;

                    group.style.display = visibleCount === 0 ? 'none' : '';
                });
            }

            // ---------- HANDLE USER CHANGES ----------
            roomCheckboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    // user interaction = commit state
                    activeRooms = new Set(
                        roomCheckboxes
                            .filter(c => c.checked)
                            .map(c => c.dataset.room)
                    );

                    localStorage.setItem(STORAGE_KEY, JSON.stringify([...activeRooms]));

                    applyRoomColumns();
                    applyUnplacedFilter();
                });
            });

            // ---------- INITIAL RUN ----------
            applyRoomColumns();
            applyUnplacedFilter();
        });
    </script>



@endsection
