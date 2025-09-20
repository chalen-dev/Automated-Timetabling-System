@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <h1>Login</h1>
    <form action="{{ url('login') }}" method="post">
        @csrf
        <input type="text" name="username_email" placeholder="Username or Email">
        @error('username_email')
            <span class="text-red-500">{{$message}}</span>
        @enderror
        <input type="password" name="password" placeholder="Password">
        @error('password')
            <span class="text-red-500">{{$message}}</span>
        @enderror
        <button type="submit">Login</button>
    </form>
@endsection
