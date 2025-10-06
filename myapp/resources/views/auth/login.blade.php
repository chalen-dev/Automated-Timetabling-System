@extends('app')

@section('title', 'Login')

@section('content')

    <div class="flex flex-col items-center justify-center w-100vh h-100% pb-10 content-around">
        <form action="{{ url('login') }}" method="post" class="flex flex-col w-90 justify-center rounded-xl items-center p-5 gap-3 bg-white shadow-2xl">
            @csrf
            <h1 class="font-bold p-3">Login to FaculTime</h1>
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
                elementId="login_password"
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

            <p>Don't have an account? <a href="{{ url('register') }}" class="hover:underline hover:text-[#5E0B0B]">Sign Up</a></p>

        </form>
    </div>


@endsection




