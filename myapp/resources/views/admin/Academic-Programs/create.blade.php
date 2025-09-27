@extends('pages.app')

@section('title', 'Create Academic Program')

@section('content')
    <h1>Create Academic Program</h1>
    <form action="{{route('admin.academic-programs.store')}}" method="post">
        @csrf

        <x-input.text
            label="Program Name"
            name="program_name"
        />

        <x-input.text
            label="Program Abbreviation"
            name="program_abbreviation"
        />

        <x-input.text-area
            label="Description"
            name="program_description"
            rows="4"
        />

        <button type="submit">Create</button>
    </form>

    <a href="{{route('admin.academic-programs.index')}}">Back</a>
@endsection
