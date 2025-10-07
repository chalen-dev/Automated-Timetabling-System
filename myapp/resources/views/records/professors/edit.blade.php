@extends('app')

@section('title', 'Edit Professor')

@section('content')
    <div class="flex flex-col pt-[40px] pb-[40px] pr-[50px] pl-[50px] gap-[50px] justify-center items-center bg-white rounded-2xl shadow-2xl">
        <h1 class="font-bold text-[18px]">Edit Professor</h1>

        <form action="{{ route('professors.update', $professor) }}" method="POST" class="flex flex-col gap-10 w-full">
            @csrf
            @method('PUT')

            <div class="flex justify-center gap-7 w-full">
                <!-- Left Column -->
                <div class="flex flex-col justify-center items-stretch gap-5">
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
                        label="Academic Program"
                        name="academic_program_id"
                        :options="$academic_program_options"
                        default=""
                        :value="old('academic_program_id', $professor->academic_program_id)"
                    />

                    <x-input.number
                        label="Max Unit Load"
                        name="max_unit_load"
                        :value="old('max_unit_load', $professor->max_unit_load)"
                        :min="1.0"
                        :step="0.1"
                    />

                    <x-input.number
                        label="Professor Age"
                        name="professor_age"
                        :value="old('professor_age', $professor->professor_age)"
                        :min="0"
                        :max="120"
                        :step="1"
                    />
                </div>

                <!-- Right Column -->
                <div class="flex flex-col justify-center items-stretch gap-5">
                    <x-input.radio-group
                        label="Professor Type"
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
            </div>

            <!-- Buttons with breathing room -->
            <div class="flex flex-row w-full justify-between items-center mt-[40px]">
                <a href="{{ route('professors.index') }}">
                    <button type="button" class="pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#aaa] text-[#fff] cursor-pointer font-[600]">
                        Back
                    </button>
                </a>

                <button type="submit" class="pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#5e0b0b] text-[#fff] cursor-pointer font-[600]">
                    Update
                </button>
            </div>
        </form>
    </div>
@endsection
