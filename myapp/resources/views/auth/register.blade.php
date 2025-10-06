@extends('app')

@section('title', 'Register')

@section('content')

    <div class="flex flex-col items-center justify-center w-screen ">
        <form action="{{ url('register') }}" method="post" class="flex flex-col w-90 justify-center items-center p-5 gap-1 bg-white rounded-xl">
            @csrf

            <h1>Sign Up to Facultime</h1>

            <x-input.auth-text
                label="USERNAME"
                type="text"
                name="name"
                placeholder="Choose a username"
                :value="old('name')"
            />

            <!--YET TO BE ADDED IN DATABASE-->
            <x-input.auth-text
                label="FIRST NAME"
                type="text"
                name="first_name"
                placeholder=""
                :value="old('first_name')"
            />

            <!--YET TO BE ADDED IN DATABASE-->
            <x-input.auth-text
                label="LAST NAME"
                type="text"
                name="last_name"
                placeholder=""
                :value="old('last_name')"
            />

            <x-input.auth-text
                label="EMAIL"
                type="email"
                name="email"
                placeholder="example.@umindanao.edu.ph"
                :value="old('email')"
            />

            <x-input.password-text
                label="PASSWORD"
                elementId="register_password"
                type="password"
                name="password"
                placeholder="At least 8 characters"
                :value="old('password')"
            />

            <x-input.password-text
                label="CONFIRM PASSWORD"
                elementId="password_confirmation"
                toggleId="togglePasswordConfirmation"
                type="password"
                name="password_confirmation"
                placeholder=""
            />

            <button type="submit" class="bg-[#fbcc15] text-black font-bold py-2 rounded w-full">Register ➝</button>
            <p>Already a member? <a href="{{ url('login') }}">Login here</a></p>
        </form>
    </div>

@endsection
