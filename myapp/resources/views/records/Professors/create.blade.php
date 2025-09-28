@extends('app')

@section('title', 'Create Professor')

@section('content')
    <h1>Create Professor</h1>
    <form action="{{route('records.professors.store')}}" method="post">
        @csrf

        <x-input.text
                label="First Name"
                name="first_name"
        />

        <x-input.text
                label="Last Name"
                name="last_name"
        />

        <x-input.select
                label="Academic Program"
                name="academic_program_id"
                :options="$academic_program_options"
                default=""
        />

        <x-input.select
                label="Professor Type (Regular/Non Regular)"
                name="professor_type"
                :options="[
                'regular' => 'Regular',
                'non_regular' => 'Non-Regular',
                'none' => 'None'
            ]"
        />

        <x-input.number
                label="Max Unit Load"
                name="max_unit_load"
                :default="0"
                :min="1.0"
                :step="0.1"
        />

        <x-input.number
                label="Professor Age"
                name="professor_age"
                :default="0"
                :min="0"
                :max="120"
                :step="1"
        />

        <x-input.text
                label="Position"
                name="position"
        />

        <button type="submit">Create</button>
    </form>

    <a href="{{route('records.professors.index')}}">Back</a>
@endsection
