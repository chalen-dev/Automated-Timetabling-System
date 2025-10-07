@extends('app')

@section('title', $timetable->timetable_name . ' Class Sessions')

@section('content')
    <div class="w-full pl-39 pt-23 p-4">
        <div class="flex flex-row mb-7 justify-between">
            {{-- Search bar for Session Groups --}}
            <div class="flex flex-col text-[#5e0b0b]">
                <h1 class="text-[18px] text-white">{{ $timetable->timetable_name }} Class Sessions</h1>
                <x-search-bar.search-bar :action="route('timetables.session-groups.index', $timetable)" />
            </div>

            <a href="{{ route('timetables.session-groups.create', $timetable) }}" class="flex align-center box-border pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#ffffff] text-[#5e0b0b] cursor-pointer shadow-2xl font-[600]">Add</a>
        </div>

        @foreach($sessionGroupsByProgram as $programId => $groups)
            <div class="flex justify-center items-center">
                <h2 class="font-bold text-xl">Program: {{ $groups->first()->academicProgram->program_abbreviation ?? 'Unknown' }}</h2>
            </div>


            @foreach($groups as $sessionGroup)
                <div class="flex justify-between mb-5 w-full">
                </div>
                <div class="pt-4 flex flex-row justify-between w-full bg-gray-100 rounded-tl-[12px] rounded-tr-[12px]">
                    <div class="pl-6 pb-1">
                        <p class="font-bold"> {{ $sessionGroup->academicProgram->program_abbreviation ?? 'Unknown' }} {{$sessionGroup->session_name }} {{ $sessionGroup->year_level }} Year</p>
                    </div>
                    <div class="pr-6 pb-1">
                        <div class="flex gap-3">
                            <a href="{{ route('timetables.session-groups.course-sessions.create', [$timetable, $sessionGroup]) }}">Add Sessions</a>
                            <a href="{{ route('timetables.session-groups.show', [$timetable, $sessionGroup]) }}">
                                <i class="bi-card-list"></i>
                            </a>
                            <a href="{{ route('timetables.session-groups.edit', [$timetable, $sessionGroup]) }}">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <x-buttons.delete
                                action="timetables.session-groups.destroy"
                                :params="[$timetable, $sessionGroup]"
                                item_name="session"
                                btnType="icon"
                            />
                        </div>
                    </div>
                </div>
                <table class="w-full text-left border-separate border-spacing-0 bg-white rounded-bl-[12px] rounded-br-[12px] shadow-md overflow-hidden">
                    <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Course Title</th>
                        <th class="px-6 py-3 font-semibold">Course Name</th>
                        <th class="px-6 py-3 font-semibold">Units</th>
                        <th class="px-6 py-3 font-semibold">Type</th>
                        <th class="px-6 py-3 font-semibold">Academic Term</th>
                        <th class="px-6 py-3 font-semibold text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody class="text-gray-700">
                    @foreach($courseSessionsBySessionGroup[$sessionGroup->id] ?? [] as $courseSession)
                        <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3">{{ $courseSession->course->course_title ?? 'Unknown Course' }}</td>
                            <td class="px-6 py-3">{{ $courseSession->course->course_name }}</td>
                            <td class="px-6 py-3">{{ $courseSession->course->unit_load }}</td>
                            <td class="px-6 py-3">{{ $courseSession->course->course_type }}</td>
                            <td class="px-6 py-3">
                                <form method="POST"
                                      action="{{ route('timetables.session-groups.course-sessions.update-term', [$timetable, $sessionGroup, $courseSession]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select
                                        name="academic_term[{{ $courseSession->id }}]"
                                        onchange="this.form.submit()"
                                        @if($courseSession->course->duration_type === 'semestral') disabled @endif
                                        class="border border-gray-300 rounded-md text-sm px-2 py-1 focus:ring-2 focus:ring-maroon-600 focus:outline-none"
                                    >
                                        @if($courseSession->course->duration_type === 'semestral')
                                            <option value="semestral" selected>semestral</option>
                                        @else
                                            <option value="" {{ is_null($courseSession->academic_term) ? 'selected' : '' }}>-- Select Term --</option>
                                            <option value="1st" {{ $courseSession->academic_term == '1st' ? 'selected' : '' }}>1st</option>
                                            <option value="2nd" {{ $courseSession->academic_term == '2nd' ? 'selected' : '' }}>2nd</option>
                                            <option value="semestral" {{ $courseSession->academic_term == 'semestral' ? 'selected' : '' }}>semestral</option>
                                        @endif
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-3 text-center">
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

            @endforeach
        @endforeach
    </div>

@endsection
