@extends('app')
@section('title', $timetable->timetable_name)

@section('content')
    <div class="flex flex-col w-full p-4 pt-23 pl-39 text-gray-800">

        @if (!empty($tableData) && isset($tableData[0]))
            <div class="overflow-x-auto bg-white rounded-lg shadow-md">
                <table class="min-w-full border-collapse table-auto text-xs md:text-sm">
                    <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                    <tr>
                        @foreach ($tableData[0] as $colIndex => $cell)
                            @if ($colIndex === 0)
                                <!-- First cell is empty -->
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
                        @if ($rowIndex === 0) @continue @endif <!-- Skip header row -->
                        <tr class="{{ $rowIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-gray-100 transition-colors">
                            @foreach ($row as $colIndex => $cell)
                                @php
                                    $span = $rowspanData[$colIndex][$rowIndex] ?? 1;
                                    $cellColor = null;
                                @endphp

                                @if ($colIndex === 0)
                                    <!-- Time periods column: show value but no color -->
                                    <td class="border border-gray-200 px-3 py-2 text-center text-sm">
                                        {{ $cell }}
                                    </td>
                                @else
                                    @php
                                        // Only apply color for non-vacant, non-time cells
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

                                        // Extract display names
                                        $displayName = $cell;
                                        $courseTitle = '';
                                        $parts = explode('_', $cell);
                                        if(count($parts) === 4){
                                            [$programAbbr, $sessionName, $sessionGroupId, $courseSessionId] = $parts;
                                            $sessionGroup = \App\Models\SessionGroup::find($sessionGroupId);
                                            $courseSession = \App\Models\CourseSession::find($courseSessionId);
                                            $courseTitle = $courseSession->course->course_title ?? '';
                                            $displayName = ($sessionGroup->academicProgram->program_abbreviation ?? $programAbbr) . ' ' .
                                                           ($sessionGroup->session_name ?? $sessionName) . ' ' .
                                                           ($sessionGroup->year_level ?? $parts[1]) . ' Year';
                                        }
                                    @endphp

                                    @if ($span > 0)
                                        <td class="border border-gray-200 px-3 py-2 text-center text-sm" rowspan="{{ $span }}" @if($cellColor) style="background-color: {{ $cellColor }};" @endif>
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
@endsection
