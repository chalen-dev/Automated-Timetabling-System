@extends('layouts.app')

@section('title', 'Dashboard')

@section('body')
    @include('dashboard.dashboard-menu')
    <h1>Welcome {{$user->name}}!</h1>
    <div class="content">
        @yield('content')
    </div>
@endsection

