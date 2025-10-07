@extends('app')

@section('title', $timetable->timetable_name)

@section('content')
    <div class="flex flex-col w-full p-4 text-white">
        <h1 class="text-2xl font-bold mb-2">Timetable View</h1>
        <p class="mb-4">
            {{ $timetable->timetable_name }} — {{ $timetable->semester }} semester ({{ $timetable->academic_year }})
        </p>

        @if (!empty($tableData) && isset($tableData[0]))
            <div class="overflow-x-auto bg-[#1e1e2f] rounded-lg shadow-md">
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
                        <tr class="{{ $rowIndex % 2 === 0 ? 'bg-[#252536]' : 'bg-[#1e1e2f]' }}">
                            @foreach ($row as $colIndex => $cell)
                                @php $span = $rowspanData[$colIndex][$rowIndex] ?? 1; @endphp
                                @if ($span > 0)
                                    <td class="border border-gray-700 px-1 py-1 text-center text-[0.65rem] md:text-sm whitespace-normal break-words" rowspan="{{ $span }}">
                                        @if (strtolower(trim($cell)) === 'vacant')
                                            <span class="text-gray-400 italic">Vacant</span>
                                        @else
                                            {{ $cell }}
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
