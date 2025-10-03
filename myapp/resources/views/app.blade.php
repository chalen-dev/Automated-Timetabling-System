

<!doctype html>
<html lang="en" x-data>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My Laravel App')</title>
    @vite('resources/css/app.css')
    <script src="//unpkg.com/alpinejs" defer></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('sidebar', { open: false }) // start hidden
        })
    </script>
</head>
<body class="overflow-x-hidden">

@include('includes.notif.flash-message')

<!-- HEADER -->
<header>
    @guest
        @include('components.headers.guest-header')
    @endguest
    @auth
        @if(request()->routeIs('timetables.timetable-editing-pane.index'))
            @include('components.headers.timetabling-header')
        @else
            @include('components.headers.auth-header')
        @endif
    @endauth
</header>
<!--Varying padding top values for varying headers-->
<div class="flex h-screen {{ request()->routeIs('timetables.timetable-editing-pane.index') ? 'pt-0' : 'pt-16' }}">
    <!-- SIDEBAR -->
    @auth
        @if(request()->routeIs('timetables.timetable-editing-pane.index'))
            <x-sidebars.timetabling-sidebar />
        @else
            <x-sidebars.sidebar />
        @endif
    @endauth

    <!-- MAIN CONTENT -->
    <main class="flex-1 transition-all p-6">
        @yield('content')
        @guest
            @include('components.footers.footer')
        @endguest
    </main>
</div>

</body>
</html>
