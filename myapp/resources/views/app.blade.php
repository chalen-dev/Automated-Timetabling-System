<!doctype html>
<html lang="en" x-data>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>@yield('title', 'My Laravel App')</title>
    @vite('resources/css/app.css')
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('sidebar', { open: false }) // start hidden
        })
    </script>
</head>
<body class="bg-page h-full">

@include('includes.notif.flash-message')

<div class="p-5">

    <!-- HEADER -->
    <header>
        @guest
            @include('components.headers.guest-header')
        @endguest
        @auth
            @if (request()->routeIs('timetables.*.*'))
                @include('components.headers.timetabling-header')
            @else
                @include('components.headers.auth-header')
            @endif
        @endauth
    </header>

    <!-- MAIN AREA -->
    @auth
    <div class="flex pt-24">
        <!-- Sidebar only for authenticated users -->
            @if(request()->routeIs('timetables.*.*'))
                <x-sidebars.timetabling-sidebar :timetable="request()->route('timetable')" />
            @else
                <x-sidebars.sidebar />
            @endif
        <!-- CONTENT for everyone -->
        <main class="flex-1 p-5">
            @yield('content')
        </main>
    </div>
    @endauth
    @guest
        <div class="flex pt-18">
            <!-- CONTENT for everyone -->
            <main class="flex-1 p-5">
                @yield('content')
            </main>
        </div>
    @endguest
</div>

<!-- FOOTER for guests only -->
@guest
    @include('components.footers.footer')
@endguest

<!-- SCRIPTS ALWAYS BEFORE </body> TAG -->
@stack('scripts')
</body>
</html>
