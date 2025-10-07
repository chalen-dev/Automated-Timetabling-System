@extends('app')

@section('title', 'Create Academic Program')

@section('content')
    <div class="flex flex-col pt-[40px] pb-[40px] pr-[50px] pl-[50px] gap-[50px] justify-center items-center bg-white rounded-2xl shadow-2xl w-full">
        <h1 class="font-bold text-[18px]">Create Academic Program</h1>
        <form class="flex flex-col w-full gap-[40px]" action="{{ route('academic-programs.store') }}" method="POST">
            @csrf

            <!-- Horizontal layout for text inputs -->
            <div class="flex gap-7 justify-center w-full">
                <x-input.text
                    label="Program Name"
                    name="program_name"
                    class="flex-1"
                />

                <x-input.text
                    label="Program Abbreviation"
                    name="program_abbreviation"
                    class="flex-1"
                />
            </div>

            <!-- Text area below -->
            <div class="flex justify-center w-full">
                <x-input.text-area
                    label="Description"
                    name="program_description"
                    rows="4"
                    class="w-full"
                />
            </div>

            <!-- Buttons with breathing room -->
            <div class="flex w-full justify-between items-center mt-[20px]">
                <a href="{{ route('academic-programs.index') }}">
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
