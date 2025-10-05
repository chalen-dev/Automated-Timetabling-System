@extends('app')

@section('title', $timetable->timetable_name . ' Class Sessions')

@section('content')
    <h1>{{$timetable->timetable_name}} Class Sessions</h1>
    <a href="{{route('timetables.session-groups.create', $timetable)}}">Add</a>
    @foreach($sessionGroupsByProgram as $programId => $groups)
        <h2>
            Program: {{ $groups->first()->academicProgram->program_abbreviation ?? 'Unknown' }}
        </h2>
        <ul>
            @foreach($groups as $sessionGroup)
                <li class="flex flex-col w-full">
                    <div class="flex justify-between w-full">
                        <p>{{ $sessionGroup->session_name }}</p>
                        <div class="flex gap-2">
                            <a href="{{ route('timetables.session-groups.course-sessions.create', [$timetable, $sessionGroup]) }}">
                                Add CourseSession
                            </a>
                            <a href="{{ route('timetables.session-groups.edit', [$timetable, $sessionGroup]) }}">Edit</a>
                            <x-buttons.delete
                                action="timetables.session-groups.destroy"
                                :params="[$timetable, $sessionGroup]"
                                item_name="session"
                                btnType="icon"
                            />
                        </div>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <td>Course Title</td>
                                <td>Course Name</td>
                                <td>Units</td>
                                <td>Type</td>
                                <td>Academic Term</td>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($courseSessionsBySessionGroup[$sessionGroup->id] ?? [] as $courseSession)
                            <tr>
                                <td>{{ $courseSession->course->course_title ?? 'Unknown Course' }}</td>
                                <td>{{ $courseSession->course->course_name }}</td>
                                <td>{{$courseSession->course->unit_load}}</td>
                                <td>{{$courseSession->course->course_type}}</td>
                                <td>
                                    <form method="POST" action="{{ route('timetables.session-groups.course-sessions.update-term', [$timetable, $sessionGroup, $courseSession]) }}">
                                        @csrf
                                        @method('PATCH')

                                        <select
                                            name="academic_term[{{ $courseSession->id }}]"
                                            onchange="this.form.submit()"
                                            @if($courseSession->course->duration_type === 'semestral') disabled @endif
                                        >
                                            @if($courseSession->course->duration_type === 'semestral')
                                                <option value="semestral" selected>semestral</option>
                                            @else
                                                <option value="">-- Select Term --</option>
                                                <option value="1st" {{ $courseSession->academic_term == '1st' ? 'selected' : '' }}>1st</option>
                                                <option value="2nd" {{ $courseSession->academic_term == '2nd' ? 'selected' : '' }}>2nd</option>
                                                <option value="semestral" {{ $courseSession->academic_term == 'semestral' ? 'selected' : '' }}>semestral</option>
                                            @endif
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <x-buttons.delete
                                        action="timetables.session-groups.course-sessions.destroy"
                                        :params="[$timetable, $sessionGroup, $courseSession]"
                                        item_name="course session"
                                        btnType="icon"
                                    />
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{-- Nested CourseSessions --}}

                </li>
            @endforeach
        </ul>
    @endforeach



@endsection
