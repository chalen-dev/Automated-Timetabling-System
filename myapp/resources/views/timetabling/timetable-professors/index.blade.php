@extends('app')

@section('title', 'Assigned Professors')

@section('content')
    <h1>Assigned Professors</h1>
    <a href="{{route('timetables.timetable-professors.create', $timetable)}}">Add</a>
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
                <td>{{$professor->last_name}}, {{$professor->first_name}}</td>
                <td>{{$professor->academicProgram?->program_abbreviation ?? 'N/A'}} </td>
                <td>{{$professor->professor_type}}</td>
                <td>0/{{$professor->max_unit_load}}</td>
                <td>{{$professor->specializations->pluck('course.course_title')->implode(', ') ?: 'N/A'}}</td>
                <td>
                    <x-buttons.delete
                        action="timetables.timetable-professors.destroy"
                        :params="[$timetable, $professor]"
                        item_name="professor"
                        btn_type="icon"
                    />
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
