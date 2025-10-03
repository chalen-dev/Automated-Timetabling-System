@extends('app')

@section('title', $timetable->timetable_name . ' Class Sessions')

@section('content')
    <h1>{{$timetable->timetable_name}} Class Sessions</h1>
    <a href="{{route('timetables.session-groups.create', $timetable)}}">Add</a>
    <table>
        <thead>

        </thead>
    </table>
    <ul>
        @foreach($sessionGroups as $programId => $groups)
            <h2>
                Program: {{ $groups->first()->academicProgram->program_abbreviation ?? 'Unknown' }}
            </h2>
            <ul>
                @foreach($groups as $sessionGroup)
                    <li class="flex w-full">
                        <p>{{ $sessionGroup->session_name }}</p>
                        <a href="{{ route('timetables.session-groups.edit', [$timetable, $sessionGroup]) }}">Edit</a>
                        <x-buttons.delete
                            action="timetables.session-groups.destroy"
                            :params="[$timetable, $sessionGroup]"
                            item_name="session"
                            btnType="icon"
                        />
                    </li>
                @endforeach
            </ul>
        @endforeach
    </ul>
@endsection
