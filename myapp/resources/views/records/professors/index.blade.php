@extends('app')

@section('title', 'Professors')

@section('content')
    <div class="flex justify-between">
        <h1>List of Professors</h1>
        <a href="{{route('professors.create')}}">Create</a>
    </div>

    <table class="w-full">
        <thead>
            <tr>
                <td>First Name</td>
                <td>Last Name</td>
                <td>Regular/Non-Regular</td>
                <td>Academic Program</td>
                <td>Max Unit Load</td>
                <td>Course Specializations</td>
                <td></td>
            </tr>
        </thead>
        <tbody>
            @foreach($professors as $professor)
            <tr>
                <td class="flex-2">{{$professor->first_name}}</td>
                <td class="flex-2">{{$professor->last_name}}</td>
                <td class="flex-2">{{$professor->professor_type}}</td>
                <td class="flex-2">{{$professor->academicProgram?->program_abbreviation ?? 'N/A'}}</td>
                <td class="flex-2">{{$professor->max_unit_load}}</td>
                <td class="flex-2">
                    {{ $professor->courses->pluck('course_title')->implode(', ') ?: 'N/A' }}
                </td>
                <td class="whitespace-nowrap w-fit px-2">
                    <div class="flex flex-row gap-10 justify-end">
                        <div class="flex flex-row gap-2 justify-center items-center">
                            <a class="flex items-center justify-center" href="{{route('professors.specializations.index', $professor)}}">Specializations</a>
                        </div>
                        <div class="flex flex-row gap-2">
                            <a class = 'flex items-center justify-center w-10 h-10' href="{{route('professors.show', $professor)}}">
                                <i class="bi-card-list"></i>
                            </a>
                            <a class = 'flex items-center justify-center w-10 h-10' href="{{route('professors.edit', $professor)}}">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <x-buttons.delete
                                action="professors.destroy"
                                :params='$professor'
                                item_name='professor'
                                btnType='icon'/>
                        </div>

                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

@endsection
