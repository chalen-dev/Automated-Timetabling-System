@extends('pages.app')

@section('title', 'Register')

@section('content')
    <h1>Register</h1>

    <form action="{{ url('register') }}" method="post">
        @csrf

        <x-auth-text-input
            label=""
            type="text"
            name="name"
            placeholder="Username"
        />

        <x-auth-text-input
            label=""
            type="email"
            name="email"
            placeholder="Email"
        />

        <x-auth-text-input
            label=""
            type="password"
            name="password"
            placeholder="Password"
        />

        <x-auth-text-input
            label=""
            type="password"
            name="password_confirmation"
            placeholder="Confirm Password"
        />

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Register</button>

    </form>
@endsection
