<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'My Laravel App')</title>
    @vite('resources/css/app.css')
    <script src="//unpkg.com/alpinejs" defer></script>

</head>
<body>
    @include('includes.notif.flash-message')
    <header>
        @guest
            @include('components.headers.guest-header')
        @endguest
        @auth
            @include('components.headers.auth-header')
        @endauth
    </header>
    <div class="container">
        <div class="flex gap-4 flex-row">
            @auth
                <x-sidebar.sidebar/>
            @endauth
            <div>
                @yield('content')
            </div>
        </div>
        @include('components.footers.footer')
    </div>
    @stack('scripts')
</body>
</html>
