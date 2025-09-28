@extends('app')

@section('title', 'Register')

@section('content')
    <h1>Register</h1>

    <form action="{{ url('register') }}" method="post">
        @csrf

        <x-input.auth-text
            label="Username"
            type="text"
            name="name"
            placeholder=""
            :value="old('name')"
        />

        <x-input.auth-text
            label="First Name"
            type="text"
            name="first_name"
            placeholder=""
            :value="old('first_name')"
        />

        <x-input.auth-text
            label="Last Name"
            type="text"
            name="last_name"
            placeholder=""
            :value="old('last_name')"
        />

        <x-input.auth-text
            label="Email"
            type="email"
            name="email"
            placeholder=""
            :value="old('email')"
        />

        <x-input.password-text
            label="Password"
            elementId="password"
            type="password"
            name="password"
            placeholder=""
            :value="old('password')"
        />

        <x-input.password-text
            label="Confirm Password"
            elementId="password_confirmation"
            toggleId="togglePasswordConfirmation"
            type="password"
            name="password_confirmation"
            placeholder=""
        />

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Register</button>
        <p>Already a member? <a href="{{ url('login') }}">Login here</a></p>
    </form>
@endsection
