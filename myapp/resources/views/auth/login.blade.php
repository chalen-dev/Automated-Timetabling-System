@extends('pages.app')

@section('title', 'Login')

@section('content')
    <h1>Login</h1>

    <form action="{{ url('login') }}" method="post" class="flex flex-col">
        @csrf

        <x-input.auth-text
            label=""
            type="text"
            name="login"
            placeholder="Username or Email"
            class="border border-gray-300 rounded"
            :value="old('login')"
        />

        <x-input.auth-text
            label=""
            type="password"
            name="password"
            placeholder="Password"
            class="border border-gray-300 rounded"
            :value="old('password')"
        />

        @if($errors->has('login_error'))
            <div class="!text-red-500 mt-1">{{$errors->first('login_error')}}</div>
        @endif

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Login</button>



    </form>
@endsection
