@extends('app')

@section('title', $timetable->timetable_name . ' Class Sessions')

@section('content')
    <h1>{{$timetable->timetable_name}} Class Sessions</h1>
    <a href="{{route('timetables.session-groups.create', $timetable)}}">Add</a>
    <table>
        <thead>

        </thead>
    </table>
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

                    {{-- Nested CourseSessions --}}
                    <ul class="ml-6 mt-2">
                        @foreach($courseSessionsBySessionGroup[$sessionGroup->id] ?? [] as $courseSession)
                            <li class="flex items-center gap-2">
                                {{ $courseSession->course->course_title ?? 'Unknown Course' }}

                                <form method="POST" action="{{ route('timetables.session-groups.course-sessions.update-term', [$timetable, $sessionGroup, $courseSession]) }}">
                                    @csrf
                                    @method('PATCH') <!-- tells Laravel this is a PATCH request -->

                                    <select name="academic_term" onchange="this.form.submit()">
                                        <option value="">-- Select Term --</option>
                                        <option value="1st" {{ $courseSession->academic_term == '1st' ? 'selected' : '' }}>1st</option>
                                        <option value="2nd" {{ $courseSession->academic_term == '2nd' ? 'selected' : '' }}>2nd</option>
                                        <option value="semestral" {{ $courseSession->academic_term == 'semestral' ? 'selected' : '' }}>semestral</option>
                                    </select>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endforeach
        </ul>
    @endforeach



@endsection
