@extends('app')

@section('title', 'Create Professor')

@section('content')
    <div class="flex flex-col pt-[40px] pb-[40px] pr-[50px] pl-[50px] gap-[50px] justify-center items-center bg-white rounded-2xl shadow-2xl">
        <h1 class="font-bold text-[18px]">Create Professor</h1>

        <form action="{{ route('professors.store') }}" method="POST" class="flex flex-col gap-10 w-full">
            @csrf

            <div class="flex justify-center gap-7 w-full">
                <!-- Left Column -->
                <div class="flex flex-col justify-center items-stretch gap-5">
                    <livewire:input.text
                        label="First Name"
                        name="first_name"
                    />

                    <livewire:input.text
                        label="Last Name"
                        name="last_name"
                    />

                    <x-input.select
                        label="Academic Program"
                        name="academic_program_id"
                        :options="$academic_program_options"
                        default=""
                    />

                    <livewire:input.number
                        label="Max Unit Load"
                        name="max_unit_load"
                        :default="0"
                        :min="1.0"
                        :step="0.1"
                    />

                    <livewire:input.number
                        label="Professor Age"
                        name="professor_age"
                        :default="0"
                        :min="0"
                        :max="120"
                        :step="1"
                    />
                </div>

                <!-- Right Column -->
                <div class="flex flex-col justify-center items-stretch gap-5">
                    <livewire:input.radio-group
                        label="Professor Type"
                        name="professor_type"
                        :options="$professorTypeOptions"
                    />

                    <livewire:input.radio-group
                        label="Gender"
                        name="gender"
                        :options="$genderOptions"
                    />

                    <livewire:input.text
                        label="Position"
                        name="position"
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
                    Create
                </button>
            </div>
        </form>
    </div>
@endsection
