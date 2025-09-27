@extends('pages.app')

@section('title', 'Edit Academic Program')

@section('content')
    <h1>Edit Academic Program</h1>
    <form action="{{route('admin.academic-programs.update', $academicProgram)}}" method="POST">
        @csrf
        @method('PUT')

        <x-input.text
            label="Program Name"
            name="program_name"
            :value="old('program_name', $academicProgram->program_name)"
        />

        <x-input.text
            label="Program Abbreviation"
            name="program_abbreviation"
            :value="old('program_abbreviation', $academicProgram->program_abbreviation)"
        />

        <x-input.text-area
            label="Description"
            name="program_description"
            rows="4"
            :value="old('program_description', $academicProgram->program_description)"
        />

        <button type="submit">Update</button>
    </form>
    <a href="{{route('admin.academic-programs.index')}}">Back</a>
@endsection
