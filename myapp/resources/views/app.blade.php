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

    <style>
        /*  For x-cloak in alpine js  */
        [x-cloak] { display: none !important; }
    </style>

</head>
<body class="bg-page h-full">

<div class="p-5">

    <!-- HEADER -->
    <livewire:partials.headers/>
    <!-- MAIN AREA -->
    @auth
    <div class="flex pt-24">
        <livewire:partials.left-sidebars/>
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
    <livewire:partials.footer/>
@endguest

<!-- Livewire -->
@livewireScripts

<!-- Sweet Alert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<x-notifications/>

<!-- Scripts -->
@stack('scripts')

</body>
</html>
