@extends('pages.app')

@section('title', 'Register')

@section('content')
    <h1>Register</h1>

    <form action="{{ url('register') }}" method="post">
        @csrf

        <x-input.auth-text
            label=""
            type="text"
            name="name"
            placeholder="Username"
            :value="old('name')"
        />

        <x-input.auth-text
            label=""
            type="email"
            name="email"
            placeholder="Email"
            :value="old('email')"
        />

        <x-input.auth-text
            label=""
            type="password"
            name="password"
            placeholder="Password"
            :value="old('password')"
        />

        <x-input.auth-text
            label=""
            type="password"
            name="password_confirmation"
            placeholder="Confirm Password"
        />

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Register</button>

    </form>
@endsection
