@extends('app')

@section('title', 'Register')

@section('content')

    <div class="flex flex-row items-center justify-center w-100vh h-100% p-0 content-around gap-10">
        <div class="flex flex-col max-w-md text-center md:text-left mr-64">
            <h1 class="text-4xl font-extrabold mb-4 text-white drop-shadow">
                WELCOME TO <span class="text-[#fbcc15]">FACULTIME!</span>
            </h1>
            <p class="text-lg text-white/90 leading-relaxed">
                An automated timetabling system that helps schools create organized schedules by arranging teachers, rooms, and class times accurately, without conflicts, and with fewer manual steps.
            </p>
        </div>
        <div>
            <!-- Changed width here to make the form wider (reduces overall height) -->
            <form action="{{ url('register') }}" method="post" class="flex flex-col w-[480px] justify-center p-5 gap-1 bg-white rounded-xl shadow-2xl">
                @csrf

                <h1 class="font-bold p-3 text-center">Sign Up to Facultime</h1>

                <livewire:input.auth.text
                    label="USERNAME"
                    type="text"
                    name="name"
                    placeholder="Choose a username"
                    :value="old('name')"
                    isRequired
                />

                <!-- Put First Name and Last Name side-by-side to use space -->
                <div class="flex gap-3">
                    <div class="flex-1">
                        <livewire:input.auth.text
                            label="FIRST NAME"
                            type="text"
                            name="first_name"
                            placeholder=""
                            :value="old('first_name')"
                            isRequired
                        />
                    </div>
                    <div class="flex-1">
                        <livewire:input.auth.text
                            label="LAST NAME"
                            type="text"
                            name="last_name"
                            placeholder=""
                            :value="old('last_name')"
                            isRequired
                        />
                    </div>
                </div>

                <livewire:input.auth.text
                    label="EMAIL"
                    type="email"
                    name="email"
                    placeholder="example.@umindanao.edu.ph"
                    :value="old('email')"
                    isRequired
                />

                <livewire:input.select
                    label="ACADEMIC PROGRAM"
                    name="academic_program_id"
                    :options="$academicPrograms->pluck('program_abbreviation', 'id')->toArray()"
                    :value="old('academic_program_id')"
                    isRequired
                />

                <!-- Put Password and Confirm Password side-by-side -->
                <div class="flex gap-3">
                    <div class="flex-1">
                        <livewire:input.auth.password-text
                            label="PASSWORD"
                            elementId="register_password"
                            type="password"
                            name="password"
                            placeholder="At least 8 characters"
                            :value="old('password')"
                            isRequired
                        />
                    </div>
                    <div class="flex-1">
                        <livewire:input.auth.password-text
                            label="CONFIRM PASSWORD"
                            elementId="password_confirmation"
                            toggleId="togglePasswordConfirmation"
                            type="password"
                            name="password_confirmation"
                            placeholder=""
                            :value="old('password_confirmation')"
                            isRequired
                        />
                    </div>
                </div>

                <button type="submit" class="bg-[#fbcc15] text-black font-bold py-2 rounded w-full hover:cursor-pointer">Register ➝</button>
                <p class="text-center">Already a member? <a href="{{ url('login') }}" class="underline hover:font-bold text-[#5E0B0B]">Login here</a></p>
            </form>
        </div>
    </div>

@endsection
