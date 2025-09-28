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
        @include('components.headers.auth-header')
    @endauth
</header>

<div class="flex h-screen pt-16">
    <!-- SIDEBAR -->
    @auth
        <x-sidebar.sidebar />
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
