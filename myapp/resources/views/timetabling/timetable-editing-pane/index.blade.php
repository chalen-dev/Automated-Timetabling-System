@extends('app')

@section('title', $timetable->timetable_name)

@section('content')
    <div class="flex flex-col w-full p-4 pt-23 pl-39 text-white">
        @if (!empty($tableData) && isset($tableData[0]))
            <div class="overflow-x-auto bg-[#1A1A2E] rounded-lg shadow-md">
                <table class="min-w-full border-collapse border border-gray-500 table-auto w-full text-xs md:text-sm">
                    <thead class="bg-[#2e2e3f] text-yellow-300 text-[0.7rem] md:text-sm">
                    <tr>
                        @foreach ($tableData[0] as $cell)
                            <th class="border border-gray-600 px-2 py-1 text-center whitespace-normal break-words">
                                {{ $cell }}
                            </th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($tableData as $rowIndex => $row)
                        @if ($rowIndex === 0) @continue @endif
                        <tr class="{{ $rowIndex % 2 === 0 ? 'bg-[#252536]' : 'bg-[#1A1A2E]' }}">
                            @foreach ($row as $colIndex => $cell)
                                @php $span = $rowspanData[$colIndex][$rowIndex] ?? 1; @endphp
                                @if ($span > 0)
                                    <td class="border border-gray-700 px-3 py-2 text-center text-sm" rowspan="{{ $span }}">
                                        @php
                                            $parts = explode('_', $cell);
                                            if (count($parts) === 4) {
                                                [$programAbbr, $sessionName, $sessionGroupId, $courseSessionId] = $parts;

                                                $sessionGroup = \App\Models\SessionGroup::find($sessionGroupId);
                                                $courseSession = \App\Models\CourseSession::find($courseSessionId);
                                                $courseTitle = $courseSession->course->course_title ?? '';
                                                $displayName = ($sessionGroup->academicProgram->program_abbreviation ?? $programAbbr)
                                                            . ' ' . ($sessionGroup->session_name ?? $sessionName)
                                                            . ' ' . ($sessionGroup->year_level ?? $parts[1]) . ' Year';
                                            } else {
                                                $displayName = $cell;
                                                $courseTitle = '';
                                            }
                                        @endphp

                                        @if (strtolower(trim($cell)) === 'vacant')
                                            <span class="text-gray-500 italic">Vacant</span>
                                        @else
                                            <div>{{ $displayName }}</div>
                                            @if (!empty($courseTitle))
                                                <div class="text-xs italic mt-1">{{ $courseTitle }}</div>
                                            @endif
                                        @endif
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        @else
            <p>No timetable data available.</p>
        @endif
    </div>
@endsection
