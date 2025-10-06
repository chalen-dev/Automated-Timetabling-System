<!doctype html>
<html lang="en" x-data>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>@yield('title', 'My Laravel App')</title>
    @vite('resources/css/app.css')
    <script src="//unpkg.com/alpinejs" defer></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('sidebar', { open: false }) // start hidden
        })
    </script>
</head>
<body class="bg-page pb-3">

@include('includes.notif.flash-message')

<!-- HEADER -->
<header>
    @guest
        @include('components.headers.guest-header')
    @endguest
    @auth
        <!--If the route is any children routes of timetables route, but not the root timetable routes-->
        @if (request()->routeIs('timetables.*.*'))
            @include('components.headers.timetabling-header')
        @else
            @include('components.headers.auth-header')
        @endif
    @endauth
</header>
<!--Varying padding top values for varying headers-->
<div class="flex h-fit">
    <!-- SIDEBAR -->
    @auth
        <!--If the route is any children routes of timetables route, but not the root timetable routes-->
        @if(request()->routeIs('timetables.*.*'))
            <x-sidebars.timetabling-sidebar :timetable="request()->route('timetable')"/>
        @else
            <x-sidebars.sidebar />
        @endif
    @endauth

    <!-- MAIN CONTENT -->
    <main class="flex-1 transition-all p-6 h-fit">
        <div>
        @yield('content')
        </div>
        <div>
        @guest
            @include('components.footers.footer')
        @endguest
        </div>
    </main>
</div>

</body>
</html>
