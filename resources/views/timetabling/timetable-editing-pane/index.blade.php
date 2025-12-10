@extends('app')
@section('title', $timetable->timetable_name)

@section('content')
    <div class="flex flex-col w-full p-4 pl-39 text-gray-800 justify-center items-center">

        @php
            $activeTermIndex = $sheetIndex < 6 ? 0 : 1;
            $activeDayIndex  = $sheetIndex % 6;
        @endphp



        @if (!empty($tableData) && isset($tableData[0]))
            {{-- Term + Day selectors (like editor), flush with table (no extra bottom margin) --}}
            {{-- TERM SELECTOR --}}
            <div class="flex flex-row justify-center w-full p-3 gap-6 bg-gray-100 border-b border-gray-200 rounded-t-lg">
                <button
                    type="button"
                    data-term-index="0"
                    class="view-term-button px-6 py-2 font-semibold rounded-lg shadow transition cursor-pointer
                       {{ $activeTermIndex === 0 ? 'bg-red-700 text-white hover:bg-red-800' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    1st Term
                </button>

                <button
                    type="button"
                    data-term-index="1"
                    class="view-term-button px-6 py-2 font-semibold rounded-lg shadow transition cursor-pointer
                       {{ $activeTermIndex === 1 ? 'bg-red-700 text-white hover:bg-red-800' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    2nd Term
                </button>
            </div>

            {{-- DAY SELECTOR --}}
            <div class="grid grid-cols-6 w-full bg-white p-3 gap-2 border-t border-gray-200">
                @foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $index => $day)
                    <button
                        type="button"
                        data-day-index="{{ $index }}"
                        class="view-day-button py-2 text-center rounded-lg text-sm font-medium shadow-md cursor-pointer transition-all duration-200
                           {{ $activeDayIndex === $index ? 'bg-red-700 text-white' : 'bg-gray-200 text-gray-700 hover:bg-red-700 hover:text-white hover:shadow-lg' }}">
                        {{ $day }}
                    </button>
                @endforeach
            </div>
            {{-- Table attaches directly to selector (shared border, no gap) --}}
            <div class="overflow-x-auto bg-white rounded-b-lg border border-gray-200 border-t-0 shadow-md">
                <table class="min-w-full border-collapse table-auto text-xs md:text-sm">
                    <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                    <tr>
                        @foreach ($tableData[0] as $colIndex => $cell)
                            @if ($colIndex === 0)
                                <th class="border border-gray-200 px-3 py-2 text-center"></th>
                            @else
                                <th class="border border-gray-200 px-3 py-2 text-center whitespace-normal break-words">
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

                                                    if (!empty($sessionGroup->session_color)) {
                                                        $cellColor = $sessionGroup->session_color;
                                                    }
                                                }

                                                if ($courseSession && $courseSession->course) {
                                                    $courseTitle = $courseSession->course->course_title ?? '';
                                                }
                                            }

                                            if ($cellColor === null) {
                                                $availableColors = $colors;

                                                if (isset($cellColors[$rowIndex - 1][$colIndex])) {
                                                    $availableColors = array_diff($availableColors, [$cellColors[$rowIndex - 1][$colIndex]]);
                                                }
                                                if (isset($cellColors[$rowIndex][$colIndex - 1])) {
                                                    $availableColors = array_diff($availableColors, [$cellColors[$rowIndex][$colIndex - 1]]);
                                                }

                                                $cellColor = $availableColors[array_rand($availableColors)] ?? $colors[0];
                                            }

                                            for ($r = $rowIndex; $r < $rowIndex + $span; $r++) {
                                                $cellColors[$r][$colIndex] = $cellColor;
                                            }
                                        }
                                    @endphp

                                    @if ($span > 0)
                                        <td class="border border-gray-200 px-3 py-2 text-center text-sm"
                                            rowspan="{{ $span }}"
                                            @if($cellColor) style="background-color: {{ $cellColor }};" @endif>

                                            @if (strtolower(trim($cell)) === 'vacant')
                                                <span class="text-gray-400 italic">Vacant</span>
                                            @else
                                                <div class="font-semibold">{{ $displayName }}</div>

                                                @if (!empty($courseTitle))
                                                    <div class="text-xs italic mt-1">{{ $courseTitle }}</div>
                                                @endif
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const baseUrl = @json(route('timetables.timetable-editing-pane.index', ['timetable' => $timetable->id]));
            const totalSheets = {{ (int) $totalSheets }};
            let activeTermIndex = {{ (int) $activeTermIndex }};
            let activeDayIndex  = {{ (int) $activeDayIndex }};

            function goToView(termIndex, dayIndex) {
                const sheetIndex = termIndex * 6 + dayIndex;
                if (sheetIndex < 0 || sheetIndex >= totalSheets || sheetIndex >= 12) {
                    return;
                }
                const url = baseUrl + '?sheet=' + sheetIndex;
                window.location.href = url;
            }

            document.querySelectorAll('.view-term-button').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const idx = parseInt(btn.getAttribute('data-term-index'), 10);
                    if (isNaN(idx) || idx === activeTermIndex) return;
                    activeTermIndex = idx;
                    goToView(activeTermIndex, activeDayIndex);
                });
            });

            document.querySelectorAll('.view-day-button').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const idx = parseInt(btn.getAttribute('data-day-index'), 10);
                    if (isNaN(idx) || idx === activeDayIndex) return;
                    activeDayIndex = idx;
                    goToView(activeTermIndex, activeDayIndex);
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
@endsection
