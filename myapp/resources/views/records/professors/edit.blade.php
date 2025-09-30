@extends('app')

@section('title', 'Edit Professor')

@section('content')
    <div class="flex flex-col gap-10 justify-center items-center pl-20 pr-20">
        <h1>Edit Professor</h1>
        <form action="{{route('records.professors.update', $professor)}}" method="post" class="flex flex-col gap-10">
            @csrf
            @method('PUT')

            <div class="flex gap-10">
                <div class="flex flex-col">
                    <x-input.text
                        label="First Name"
                        name="first_name"
                        :value="$professor->first_name"
                    />

                    <x-input.text
                        label="Last Name"
                        name="last_name"
                        :value="$professor->last_name"
                    />

                    <x-input.select
                        label="Academic Program"
                        name="academic_program_id"
                        :options="$academic_program_options"
                        default=""
                        :value="$professor->academic_program_id"
                    />

                    <x-input.number
                        label="Max Unit Load"
                        name="max_unit_load"
                        :value="$professor->max_unit_load"
                        :min="1.0"
                        :step="0.1"
                    />

                    <x-input.number
                        label="Professor Age"
                        name="professor_age"
                        :value="$professor->professor_age"
                        :min="0"
                        :max="120"
                        :step="1"
                    />
                </div>
                <div class="flex flex-col">
                    <x-input.radio-group
                        label="Professor Type (Regular/Non Regular)"
                        name="professor_type"
                        :options="$professorTypeOptions"
                        :value="old('professor_type', $professor->professor_type)"
                    />

                    <x-input.radio-group
                        label="Gender"
                        name="gender"
                        :options="$genderOptions"
                        :value="old('gender', $professor->gender)"
                    />

                    <x-input.text
                        label="Position"
                        name="position"
                        :value="old('position', $professor->position)"
                    />
                </div>
                <div>

                </div>
            </div>
            <div class="flex justify-center items-center gap-100">
                <a href="{{route('records.professors.index')}}">Back</a>
                <button type="submit">Update</button>
            </div>
        </form>
    </div>
@endsection
