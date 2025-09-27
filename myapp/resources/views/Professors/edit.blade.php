@extends('pages.app')

@section('title', 'Edit Professor')

@section('content')
    <form action="{{route('professors.update', $professor)}}" method='POST' class="flex flex-col gap-4 justify-start">
        @csrf
        @method('PUT')

        <x-input.text
            label="First Name"
            name="first_name"
            :value="old('first_name', $professor->first_name)"
        />

        <x-input.text
            label="Last Name"
            name="last_name"
            :value="old('last_name', $professor->last_name)"
        />

        <x-input.select
            label="Professor Type (Regular/Non Regular"
            name="professor_type"
            :options="[
                'regular' => 'Regular',
                'non_regular' => 'Non-Regular',
                'none' => 'None'
            ]"
            :value="old('professor_type', $professor->professor_type)"
        />

        <x-input.number
            label="Max Unit Load"
            name="max_unit_load"
            :min="1.0"
            :step="0.1"
            :value="old('max_unit_load', $professor->max_unit_load)"
        />

        <x-input.number
            label="Professor Age"
            name="professor_age"
            :min="0"
            :max="120"
            :step="1"
            :value="old('professor_age', $professor->professor_age)"
        />

        <x-input.text
            label="Position"
            name="position"
            :value="old('position', $professor->position)"
        />

        <button type="submit">Confirm Changes</button>
        <a href="{{route('professors.index')}}">Back</a>
    </form>
@endsection
