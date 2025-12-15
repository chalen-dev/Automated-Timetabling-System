@extends('app')
@section('title', $timetable->timetable_name . ' - Overview')

@section('content')
    <div class="flex flex-col w-full p-4 pl-39 text-gray-800 justify-center items-center">
        <div class="w-full max-w-[1450px]">

            {{-- NEW: Filters tray (Programs / Year / Time) --}}
            @if (!empty($sessionGroupsByProgram))
                <livewire:trays.session-group-filters :sessionGroupsByProgram="$sessionGroupsByProgram" />
            @endif

            <div class="flex items-center justify-between mb-3">
                <div class="text-white font-semibold">
                    Timetable Overview (Grouped by Class Sessions)
                </div>

                <div class="text-sm text-gray-500">
                    {{ $timetable->timetable_name }}
                </div>
            </div>

            {{-- TERM SELECTOR --}}
            <div class="flex flex-row justify-center w-full p-3 gap-6 bg-gray-100 border border-gray-200 rounded-t-lg">
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
            </div>

            <div class="overflow-x-auto bg-white rounded-b-lg border border-gray-200 border-t-0 shadow-md">
                @if (!empty($error))
                    <div class="p-4 text-sm text-red-700">
                        {{ $error }}
                    </div>
                @elseif (empty($groups))
                    <div class="p-4 text-sm text-gray-500 italic">
                        No overview data found.
                    </div>
                @else
                    <table class="min-w-full border-collapse table-auto text-xs md:text-sm">
                        <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                        <tr>
                            <th class="border border-gray-200 px-3 py-2 text-left w-[420px]">
                                Course
                            </th>
                            @foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day)
                                <th class="border border-gray-200 px-3 py-2 text-center">
                                    {{ $day }}
                                </th>
                            @endforeach
                        </tr>
                        </thead>

                        <tbody class="text-gray-700">
                        @php $rowStripe = 0; @endphp

                        @foreach ($groups as $group)
                            @php
                                $gid = (int) ($group['session_group_id'] ?? 0);
                                $programId = (string) ($group['academic_program_id'] ?? 0);
                                $yearLevel = (string) ($group['year_level'] ?? '');
                                $sessionTime = (string) ($group['session_time'] ?? '');
                            @endphp

                            {{-- Group header row --}}
                            <tr
                                class="bg-gray-200 overview-group-header"
                                data-group-id="{{ $gid }}"
                                data-program-id="{{ $programId }}"
                                data-year="{{ $yearLevel }}"
                                data-time="{{ $sessionTime }}"
                            >
                                <td class="border border-gray-200 px-3 py-2 font-semibold text-gray-800" colspan="7">
                                    {{ $group['group_label'] ?? 'Session Group' }}
                                    @if (!empty($group['session_group_id']))
                                        <span class="text-xs font-normal text-gray-600 ml-2">
                                            (#{{ $group['session_group_id'] }})
                                        </span>
                                    @endif
                                </td>
                            </tr>

                            {{-- Group items --}}
                            @foreach (($group['items'] ?? []) as $item)
                                @php
                                    $rowStripe++;
                                    $rowClass = $rowStripe % 2 === 0 ? 'bg-white' : 'bg-gray-50';
                                @endphp

                                <tr class="{{ $rowClass }} hover:bg-gray-100 transition-colors overview-group-item" data-group-id="{{ $gid }}">
                                    <td class="border border-gray-200 px-3 py-2 align-top">
                                        <div class="font-semibold text-[12px] leading-tight">
                                            {{ $item['course_title'] ?? ('Course Session #' . ($item['course_session_id'] ?? '')) }}
                                        </div>
                                        @if (!empty($item['course_session_id']))
                                            <div class="text-[10px] text-gray-400 leading-tight mt-0.5">
                                                #{{ $item['course_session_id'] }}
                                            </div>
                                        @endif
                                    </td>

                                    @foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day)
                                        @php $entries = $item['days'][$day] ?? []; @endphp

                                        <td class="border border-gray-200 px-2 py-2 align-top text-[11px]">
                                            @if (empty($entries))
                                                <span class="text-gray-400 italic">Vacant</span>
                                            @else
                                                <div class="space-y-1">
                                                    @foreach ($entries as $e)
                                                        <div class="rounded-md border border-gray-200 bg-white px-2 py-1">
                                                            <div class="font-semibold text-gray-800 leading-tight">
                                                                {{ ($e['start'] ?? '') !== '' ? ($e['start'] . '–' . ($e['end'] ?? '')) : 'Time unknown' }}
                                                            </div>
                                                            <div class="text-gray-600 leading-tight">
                                                                @ {{ $e['room'] ?? 'Room unknown' }}
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const state = {
                programId: 'all',
                year: 'all',
                time: 'all'
            };

            function setActive(btns, activeBtn) {
                btns.forEach(b => {
                    b.classList.remove('bg-red-700', 'text-white');
                    b.classList.add('bg-gray-200', 'text-gray-800', 'hover:bg-gray-300');
                });

                activeBtn.classList.remove('bg-gray-200', 'text-gray-800', 'hover:bg-gray-300');
                activeBtn.classList.add('bg-red-700', 'text-white');
            }

            function groupMatches(headerEl) {
                const p = headerEl.dataset.programId || '0';
                const y = (headerEl.dataset.year || '').toLowerCase();
                const t = (headerEl.dataset.time || '').toLowerCase();

                const okProgram = (state.programId === 'all') || (String(state.programId) === String(p));
                const okYear = (state.year === 'all') || (String(state.year).toLowerCase() === y);
                const okTime = (state.time === 'all') || (String(state.time).toLowerCase() === t);

                return okProgram && okYear && okTime;
            }

            function applyFilters() {
                const headers = document.querySelectorAll('tr.overview-group-header');
                headers.forEach(header => {
                    const gid = header.dataset.groupId;
                    const items = document.querySelectorAll('tr.overview-group-item[data-group-id="' + gid + '"]');

                    const show = groupMatches(header);

                    header.style.display = show ? '' : 'none';
                    items.forEach(row => {
                        row.style.display = show ? '' : 'none';
                    });
                });
            }

            // Programs
            const programBtns = document.querySelectorAll('.program-filter-btn');
            programBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    state.programId = btn.dataset.programId || 'all';
                    setActive(programBtns, btn);
                    applyFilters();
                });
            });

            // Year
            const yearBtns = document.querySelectorAll('.year-filter-btn');
            yearBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    state.year = btn.dataset.year || 'all';
                    setActive(yearBtns, btn);
                    applyFilters();
                });
            });

            // Time
            const timeBtns = document.querySelectorAll('.time-filter-btn');
            timeBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    state.time = btn.dataset.time || 'all';
                    setActive(timeBtns, btn);
                    applyFilters();
                });
            });

            // default apply (shows all)
            applyFilters();
        });
    </script>
@endsection
