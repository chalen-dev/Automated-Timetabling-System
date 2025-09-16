@extends('layouts.app')

@section('title', 'Register')

@section('content')
    <h1>Register</h1>
    <form action="{{ url('register') }}" method="post">
        @csrf
        <input type="text" name="username" placeholder="Username">
        <input type="password" name="password" placeholder="Password">
        <button type="submit">Register</button>
    </form>
@endsection
