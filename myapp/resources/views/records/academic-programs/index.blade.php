@extends('app')

@section('title', 'Academic Programs')

@section('content')
    <h1>Academic Programs</h1>


    <!-- Search Bar Component -->
    <div class="flex justify-between mb-4">
        <x-search-bar.search-bar
            :action="route('academic-programs.index')"
            placeholder="Search by program name or abbreviation..."
            name="search"
        />
        <a href="{{ route('academic-programs.create') }}" class="bg-blue-500 text-white px-3 py-1 rounded">Create</a>
    </div>

    <table class="w-full">
        <thead>
        <tr>
            <td>Program Name</td>
            <td>Program Abbreviation</td>
            <td></td>
        </tr>
        </thead>
        <tbody>
        @foreach($academicPrograms as $academicProgram)
            <tr>
                <td>{{ $academicProgram->program_name }}</td>
                <td>{{ $academicProgram->program_abbreviation }}</td>
                <td class="whitespace-nowrap px-2">
                    <div class="flex flex-row gap-2 justify-end items-center">
                        <a class='flex items-center justify-center w-10 h-10' href="{{ route('academic-programs.show', $academicProgram) }}">
                            <i class="bi-card-list"></i>
                        </a>
                        <a class='flex items-center justify-center w-10 h-10' href="{{ route('academic-programs.edit', $academicProgram) }}">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <x-buttons.delete action="academic-programs.destroy" :params='$academicProgram' item_name='academic program' btnType='icon'/>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
