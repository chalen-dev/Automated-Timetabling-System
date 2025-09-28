@extends('app')

@section('title', 'Create Professor')

@section('content')
    <div class="flex flex-col gap-10 justify-center items-center pl-20 pr-20">
        <h1>Create Professor</h1>
        <form action="{{route('records.professors.store')}}" method="post">
            @csrf

            <div class="flex gap-10">
                <div class="flex flex-col">
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

                </div>
                <div class="flex flex-col">

                    <x-input.radio-group
                        label="Professor Type (Regular/Non Regular)"
                        name="professor_type"
                        :options="$professorTypeOptions"
                    />

                    <x-input.radio-group
                        label="Gender"
                        name="gender"
                        :options="$genderOptions"
                    />

                    <x-input.text
                        label="Position"
                        name="position"
                    />
                </div>
            </div>


            <div class="flex justify-center items-center gap-100">
                <a href="{{route('records.professors.index')}}">Back</a>
                <button type="submit">Create</button>
            </div>

        </form>


    </div>

@endsection
