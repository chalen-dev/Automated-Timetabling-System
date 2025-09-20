@extends('layouts.app')

@section('title', 'Register')

@section('body')
    <h1>Register</h1>

    <form action="{{ url('register') }}" method="post">
        @csrf

        <input type="text" name="name" placeholder="Username" class="border border-gray-300 rounded">
        @error('name')
        <span class="text-red-500">{{$message}}</span>
        @enderror
        <br>

        <input type="email" name="email" placeholder="Email" class="border border-gray-300 rounded">
        @error('email')
        <span class="text-red-500">{{$message}}</span>
        @enderror
        <br>

        <input type="password" name="password" placeholder="Password" class="border border-gray-300 rounded">
        @error('password')
        <span class="text-red-500">{{$message}}</span>
        @enderror
        <br>

        <input type="password" name="password_confirmation" placeholder="Confirm Password"
               class="border border-gray-300 rounded">
        @error('password_confirmation')
        <span class="text-red-500">{{$message}}</span>
        @enderror
        <br>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Register</button>

    </form>
@endsection
