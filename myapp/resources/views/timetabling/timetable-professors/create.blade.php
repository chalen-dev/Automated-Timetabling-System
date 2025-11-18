@extends('app')

@section('title', 'Assign Professor')

@section('content')
    @php
        // Track currently selected IDs from query string
        $selected = request()->input('professors', []);
    @endphp

    <div class="w-full p-4 pl-40">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold text-white">Choose Professors</h1>

            <!-- Search bar with preserved selections -->
            <livewire:input.search-bar :action="route('timetables.timetable-professors.create', $timetable)">
                @foreach($selected as $id)
                    <input type="hidden" name="professors[]" value="{{ $id }}">
                @endforeach
            </livewire:input.search-bar>
        </div>

        <!-- Form -->
        <form action="{{ route('timetables.timetable-professors.store', $timetable) }}" method="POST">
            @csrf

            <div class="flex gap-4 mb-4">
                <button type="submit"
                        class="bg-yellow-500 text-[#5e0b0b] px-4 py-2 rounded-lg font-semibold shadow hover:bg-yellow-600 active:bg-yellow-700 transition-all duration-150">
                    Add
                </button>
                <a href="{{ route('timetables.timetable-professors.index', $timetable) }}"
                   class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold shadow hover:bg-gray-300 hover:text-gray-900 active:bg-gray-400 transition-all duration-150">
                    Back
                </a>
            </div>

            @if(isset($message))
                <div class="text-red-500 mb-4">{{ $message }}</div>
            @endif

            <table class="w-full text-left border-separate border-spacing-0 bg-white rounded-lg shadow-md overflow-hidden">
                <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3 font-semibold">Full Name</th>
                    <th class="px-6 py-3 font-semibold">Academic Program</th>
                    <th class="px-6 py-3 font-semibold">Type</th>
                    <th class="px-6 py-3 font-semibold">Current Load</th>
                    <th class="px-6 py-3 font-semibold">Specializations</th>
                    <th class="px-6 py-3 font-semibold text-center">Select</th>
                </tr>
                </thead>
                <tbody class="text-gray-700">
                @foreach($professors as $professor)
                    <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors cursor-pointer"
                        onclick="if(event.target.type !== 'checkbox') this.querySelector('input[type=checkbox]').click()">
                        <td class="px-6 py-3">{{ $professor->last_name }}, {{ $professor->first_name }}</td>
                        <td class="px-6 py-3">{{ $professor->academicProgram?->program_abbreviation ?? 'N/A' }}</td>
                        <td class="px-6 py-3">{{ $professor->professor_type }}</td>
                        <td class="px-6 py-3">0/{{ $professor->max_unit_load }}</td>
                        <td class="px-6 py-3">
                            {{ $professor->specializations->pluck('course.course_title')->implode(', ') ?: 'N/A' }}
                        </td>
                        <td class="px-6 py-3 text-center">
                            <input type="checkbox"
                                   name="professors[]"
                                   value="{{ $professor->id }}"
                                {{ in_array($professor->id, $selected) ? 'checked' : '' }}>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </form>

    </div>
@endsection
