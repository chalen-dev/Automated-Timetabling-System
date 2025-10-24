<!doctype html>
<html lang="en"> <!--Removed x-data attribute in html tag-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>@yield('title', 'My Laravel App')</title>

    <!-- Livewire -->
    @livewireStyles

    <!--CSS-->
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')

    <!-- Sweet Alert, for confirmation dialogs -->
    @include('sweetalert2::index')

    <style>
        /*  For x-cloak in alpine js  */
        [x-cloak] { display: none !important; }
    </style>

</head>
<body class="bg-page h-full">

@include('includes.notif.flash-message')

<div class="p-5">

    <!-- HEADER -->
    <livewire:header/>
    <!-- MAIN AREA -->
    @auth
    <div class="flex pt-24">
        <livewire:left-sidebar/>
    </div>
    @endauth
    <div class="flex">
        <!-- CONTENT for everyone -->
        <main class="flex-1 p-5">
            @yield('content')
        </main>
    </div>
</div>

<!-- FOOTER for guests only -->
@guest
    @include('components.footers.footer')
@endguest

<!-- Livewire -->
@livewireScripts

<!-- Scripts -->
@stack('scripts')

</body>
</html>
