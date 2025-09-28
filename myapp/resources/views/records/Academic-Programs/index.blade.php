@extends('app')

@section('title', 'Academic Programs')

@section('content')
    <h1>Academic Programs</h1>
    <a href="{{route('records.academic-programs.create')}}">Create</a>
    <ul>
        @foreach($academicPrograms as $academicProgram)
            <li class="flex gap-10">
                <p>{{$academicProgram->program_name}}</p>
                <p>{{$academicProgram->program_abbreviation}}</p>
                <a href="{{route('records.academic-programs.show', $academicProgram)}}">View</a>
                <a href="{{route('records.academic-programs.edit', $academicProgram)}}">Edit</a>
                <x-buttons.delete action="records.academic-programs.destroy" :model="$academicProgram"
                                  item_name="Academic Program"/>
            </li>
        @endforeach
    </ul>
@endsection
