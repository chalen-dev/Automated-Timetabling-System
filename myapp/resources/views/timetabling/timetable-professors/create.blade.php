@extends('app')

@section('title', 'Assign Professor')

@section('content')
    @php
        // Track currently selected IDs from query string
        $selected = request()->input('professors', []);
    @endphp

    <h1>Choose Professors</h1>

    <x-search-bar.search-bar :action="route('timetables.timetable-professors.create', $timetable)">
        @foreach($selected as $id)
            <input type="hidden" name="professors[]" value="{{ $id }}">
        @endforeach
    </x-search-bar.search-bar>

    <form action="{{ route('timetables.timetable-professors.store', $timetable) }}" method="POST">
        @csrf

        <button type="submit">Add</button>
        <a href="{{ route('timetables.timetable-professors.index', $timetable) }}">Back</a>

        @if(isset($message))
            <div class="!text-red-500">{{ $message }}</div>
        @endif

        <table class="w-full">
            <thead>
            <tr>
                <td>Full Name</td>
                <td>Academic Program</td>
                <td>Regular/Non-Regular</td>
                <td>Current Load</td>
                <td>Specializations</td>
                <td></td>
            </tr>
            </thead>
            <tbody>
            @foreach($professors as $professor)
                <tr>
                    <td>{{ $professor->last_name }}, {{ $professor->first_name }}</td>
                    <td>{{ $professor->academicProgram?->program_abbreviation ?? 'N/A' }}</td>
                    <td>{{ $professor->professor_type }}</td>
                    <td>0/{{ $professor->max_unit_load }}</td>
                    <td>{{ $professor->specializations->pluck('course.course_title')->implode(', ') ?: 'N/A' }}</td>
                    <td>
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

@endsection
