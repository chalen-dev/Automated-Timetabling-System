@extends('app')

@section('title', 'Assigned Professors')

@section('content')
    <div class="w-full pl-40 p-4"> <!-- Added left padding for sidebar -->

        <!-- Header: Title + Search + Add Button -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex gap-20 items-center justify-between">
                <h1 class="text-xl font-bold mb-0 text-white">Assigned Professors</h1>
                <livewire:input.search-bar
                    :action="route('timetables.timetable-professors.index', $timetable)"
                    placeholder="Search professors..."
                />
            </div>

            <a href="{{ route('timetables.timetable-professors.create', $timetable) }}"
               class="bg-yellow-500 text-[#5e0b0b] px-4 py-2 rounded-lg font-semibold shadow hover:bg-yellow-600 active:bg-yellow-700 transition-all duration-150">
                Add
            </a>
        </div>

        <!-- Table -->
        <table class="w-full text-left border-separate border-spacing-0 bg-white rounded-lg shadow-md overflow-hidden">
            <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
            <tr>
                <th class="px-6 py-3 font-semibold">Full Name</th>
                <th class="px-6 py-3 font-semibold">Academic Program</th>
                <th class="px-6 py-3 font-semibold">Regular/Non-Regular</th>
                <th class="px-6 py-3 font-semibold">Current Load</th>
                <th class="px-6 py-3 font-semibold">Specializations</th>
                <th class="px-6 py-3 font-semibold text-center">Action</th>
            </tr>
            </thead>
            <tbody class="text-gray-700">
            @foreach($professors as $professor)
                <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-3">{{ $professor->last_name }}, {{ $professor->first_name }}</td>
                    <td class="px-6 py-3">{{ $professor->academicProgram?->program_abbreviation ?? 'N/A' }}</td>
                    <td class="px-6 py-3">{{ $professor->professor_type }}</td>
                    <td class="px-6 py-3">0/{{ $professor->max_unit_load }}</td>
                    <td class="px-6 py-3">{{ $professor->specializations->pluck('course.course_title')->implode(', ') ?: 'empty' }}</td>
                    <td class="px-6 py-3 text-center">
                        <livewire:buttons.delete
                            action="timetables.timetable-professors.destroy"
                            :params="[$timetable, $professor]"
                            item_name="professor"
                            btnType="icon"
                            class="text-gray-600 px-3 py-2 rounded-lg hover:bg-gray-200 hover:text-gray-800 active:bg-gray-300 transition-all duration-150 flex items-center justify-center w-10 h-10"
                        />
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
