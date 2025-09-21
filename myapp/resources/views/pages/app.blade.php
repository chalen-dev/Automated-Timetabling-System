<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'My Laravel App')</title>
    @vite('resources/css/app.css')

</head>
<body>
<header>
    @guest
        @include('partials.guest-header')
    @endguest
    @auth
        @include('partials.auth-header')
    @endauth
</header>
<div class="container">
    <div class="row">
        <div>
            @auth
                @include('dashboard.dashboard-menu')
            @endauth
        </div>
        <div>
            @yield('content')
        </div>
    </div>
    @include('partials.footer')
</div>
</body>
</html>
