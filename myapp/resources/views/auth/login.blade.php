@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <h1>Login</h1>

    <form action="{{ url('login') }}" method="post">
        @csrf

        <input type="text" name="login" placeholder="Username or Email" class="border border-gray-300 rounded">
        @error('login')
            <span class="text-red-500">{{$message}}</span>
        @enderror
        <br>

        <input type="password" name="password" placeholder="Password" class="border border-gray-300 rounded">
        @error('password')
            <span class="text-red-500">{{$message}}</span>
        @enderror
        <br>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Login</button>


    </form>
@endsection
