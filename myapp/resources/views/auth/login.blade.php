@extends('app')

@section('title', 'Login')

@section('content')

    <div class="flex flex-col items-center justify-center w-screen h-[calc(100vh-55px)]">
        <form action="{{ url('login') }}" method="post" class="flex flex-col w-90 justify-center items-center p-5 gap-3">
            @csrf
            <h1>Login to FaculTime</h1>
            <br>
            <x-input.auth-text
                label="USERNAME or EMAIL"
                type="text"
                name="login"
                placeholder=""
                class="border border-gray-300 rounded"
                :value="old('login')"
            />

            <x-input.password-text
                label="PASSWORD"
                elementId="password"
                type="password"
                name="password"
                placeholder="Enter your password"
                class="border border-gray-300 rounded"
                :value="old('password')"
            />


            @if($errors->has('login_error'))
                <div class="!text-red-500 mt-1">{{$errors->first('login_error')}}</div>
            @endif

            <button type="submit" class="bg-[#fbcc15] text-black px-4 py-2 rounded font-bold w-full">Login ➝</button>

            <p>Don't have an account? <a href="{{ url('register') }}">Sign Up</a></p>

        </form>
    </div>


@endsection




