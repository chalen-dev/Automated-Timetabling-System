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

<body class="bg-page flex flex-col align-center ">

    @include('includes.notif.flash-message')

<!-- SIDEBAR -->
    @auth
        <x-sidebar.sidebar />
    @endauth

<!-- HEADER -->
        <header class="topbar">
            @guest
                @include('components.headers.guest-header')
            @endguest
            @auth
                @include('components.headers.auth-header')
            @endauth
        </header>
<!-- MAIN CONTENT -->
        <main>
            <div>
                @yield('content')
            </div>
            <div>
                @guest
                    @include('components.footers.footer')
                @endguest
            </div>
        </main>


</body>
</html>
