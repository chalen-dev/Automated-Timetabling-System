@extends('pages.app')

@section('title', 'Login')

@section('content')
    <h1>Login</h1>

    <form action="{{ url('login') }}" method="post" class="flex flex-col">
        @csrf
        <h1>Login to FaculTime</h1>
        <x-input.auth-text
            label="Username or Email"
            type="text"
            name="login"
            placeholder=""
            class="border border-gray-300 rounded"
            :value="old('login')"
        />

        <x-input.password-text
            label="Password"
            type="password"
            name="password"
            placeholder=""
            class="border border-gray-300 rounded"
            :value="old('password')"
        />



        @if($errors->has('login_error'))
            <div class="!text-red-500 mt-1">{{$errors->first('login_error')}}</div>
        @endif

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Login</button>

        <p>Don't have an account? <a href="{{ url('register') }}">Register here</a></p>

    </form>
@endsection




