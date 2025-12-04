@extends('app')

@section('title', 'Login')

@section('content')

    <div class="flex flex-row items-center justify-center w-100vh h-100% content-around gap-10">
        <div class="flex flex-col max-w-md text-center md:text-left mr-64">
            <h1 class="text-4xl font-extrabold mb-4 text-white drop-shadow">
                WELCOME TO <span class="text-[#fbcc15]">FACULTIME!</span>
            </h1>
            <p class="text-lg text-white/90 leading-relaxed">
                An automated timetabling system that helps schools create organized schedules by arranging teachers, rooms, and class times accurately, without conflicts, and with fewer manual steps.
            </p>
        </div>
        <div class="mt-35 mb-45">
        <form action="{{ url('login') }}" method="POST" class="flex flex-col w-90 content-between rounded-xl items-center p-5 gap-3 bg-white shadow-2xl">
            @csrf
            <h1 class="font-bold p-1">Login to FaculTime</h1>
            <livewire:input.auth.text
                label="USERNAME or EMAIL"
                type="text"
                name="login"
                placeholder=""
                class="border border-gray-300 rounded"
                :value="old('login')"
            />

            <livewire:input.auth.password-text
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

            <button type="submit" class="bg-[#fbcc15] text-black px-4 py-2 rounded font-bold w-full hover:cursor-pointer">Login ➝</button>

            <p>Don't have an account? <a href="{{ url('register') }}" class="underline hover:font-bold text-[#5E0B0B]">Sign Up</a></p>

        </form>
        </div>
    </div>


@endsection




