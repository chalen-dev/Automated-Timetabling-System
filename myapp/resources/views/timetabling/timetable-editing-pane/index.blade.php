@extends('app')
@section('title', $timetable->timetable_name)

@section('content')
    <div class="flex flex-col w-full p-4 pl-39 text-gray-800">

        @php
            $prevSheet = $sheetIndex > 0 ? $sheetIndex - 1 : null;
            $nextSheet = ($sheetIndex < $totalSheets - 1 && $sheetIndex < 11) ? $sheetIndex + 1 : null;
        @endphp

            <!-- Sheet Navigation -->
        <div
            class="flex justify-between items-center mb-4 p-4 bg-gradient-to-r from-blue-50 to-white rounded-xl shadow-md">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 tracking-wide">
                    Sheet: {{ $sheetDisplayName ?? ('Sheet ' . ($sheetIndex + 1)) }}
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Showing sheet {{ $sheetIndex + 1 }} of {{ min($totalSheets, 12) }}
                </p>
            </div>

            <div class="flex gap-2">
                @if ($prevSheet !== null)
                    <a id="prevBtn"
                       href="{{ route('timetables.timetable-editing-pane.index', ['timetable' => $timetable->id, 'sheet' => $prevSheet]) }}"
                       class="flex items-center gap-1 px-5 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg shadow transition duration-150">
                        <span class="text-lg">←</span> Previous
                    </a>
                @else
                    <button
                        class="flex items-center gap-1 px-5 py-2 bg-gray-100 text-gray-400 font-medium rounded-lg shadow cursor-not-allowed">
                        <span class="text-lg">←</span> Previous
                    </button>
                @endif

                @if ($nextSheet !== null)
                    <a id="nextBtn"
                       href="{{ route('timetables.timetable-editing-pane.index', ['timetable' => $timetable->id, 'sheet' => $nextSheet]) }}"
                       class="flex items-center gap-1 px-5 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg shadow transition duration-150">
                        Next <span class="text-lg">→</span>
                    </a>
                @else
                    <button
                        class="flex items-center gap-1 px-5 py-2 bg-gray-100 text-gray-400 font-medium rounded-lg shadow cursor-not-allowed">
                        Next <span class="text-lg">→</span>
                    </button>
                @endif
            </div>
        </div>


        @if (!empty($tableData) && isset($tableData[0]))
            <div class="overflow-x-auto bg-white rounded-lg shadow-md">
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
                        @if ($rowIndex === 0)
                            @continue
                        @endif
                        <tr class="{{ $rowIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-gray-100 transition-colors">
                            @foreach ($row as $colIndex => $cell)
                                @php
                                    $span = $rowspanData[$colIndex][$rowIndex] ?? 1;
                                    $cellColor = null;
                                @endphp

                                @if ($colIndex === 0)
                                    <td class="border border-gray-200 px-3 py-2 text-center text-sm">
                                        {{ $cell }}
                                    </td>
                                @else
                                    @php
                                        if ($span > 0 && strtolower(trim($cell)) !== 'vacant') {
                                            $availableColors = $colors;
                                            if (isset($cellColors[$rowIndex - 1][$colIndex])) {
                                                $availableColors = array_diff($availableColors, [$cellColors[$rowIndex - 1][$colIndex]]);
                                            }
                                            if (isset($cellColors[$rowIndex][$colIndex - 1])) {
                                                $availableColors = array_diff($availableColors, [$cellColors[$rowIndex][$colIndex - 1]]);
                                            }
                                            $cellColor = $availableColors[array_rand($availableColors)] ?? $colors[0];
                                            for ($r = $rowIndex; $r < $rowIndex + $span; $r++) {
                                                $cellColors[$r][$colIndex] = $cellColor;
                                            }
                                        }

                                        $displayName = $cell;
                                        $courseTitle = '';
                                        $parts = explode('_', $cell);
                                        if (count($parts) === 4) {
                                            [$programAbbr, $sessionName, $sessionGroupId, $courseSessionId] = $parts;
                                            $sessionGroup = \App\Models\timetabling\SessionGroup::find($sessionGroupId);
                                            $courseSession = \App\Models\timetabling\CourseSession::find($courseSessionId);
                                            $courseTitle = $courseSession->course->course_title ?? '';
                                            $displayName = ($sessionGroup->academicProgram->program_abbreviation ?? $programAbbr) . ' ' .
                                                           ($sessionGroup->session_name ?? $sessionName) . ' ' .
                                                           ($sessionGroup->year_level ?? $parts[1]) . ' Year';
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
            <p class="text-gray-500">No timetable data available.</p>
        @endif
    </div>

    <!-- Keyboard navigation script -->
    <script>
        document.addEventListener('keydown', function (e) {
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');

            if (e.key === 'ArrowLeft' && prevBtn) {
                window.location.href = prevBtn.href;
            } else if (e.key === 'ArrowRight' && nextBtn) {
                window.location.href = nextBtn.href;
            }
        });
    </script>
@endsection
