@extends('layouts.app')

@section('title', 'Register')

@section('content')
    <h1>Register</h1>
    <form action="{{ url('register') }}" method="post">
        @csrf
        <input type="text" name="username" placeholder="Username">
        @error('username')
            <span class="text-red-500">{{$message}}</span>
        @enderror
        <br>
        <input type="email" name="email" placeholder="Email">
        @error('email')
            <span class="text-red-500">{{$message}}</span>
        @enderror
        <br>
        <input type="password" name="password" placeholder="Password">
        @error('password')
            <span class="text-red-500">{{$message}}</span>
        @enderror
        <br>
        <input type="password" name="password_confirmation" placeholder="Confirm Password">
        @error('password_confirmation')
            <span class="text-red-500">{{$message}}</span>
        @enderror
        <br>
        <button type="submit">Register</button>
    </form>
@endsection
