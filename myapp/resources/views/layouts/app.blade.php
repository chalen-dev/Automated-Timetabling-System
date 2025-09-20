<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'My Laravel App')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <header>
        @include('partials.header')
    </header>
    <div class="container">
        <div class="row">
            <div></div>
            <div>
                @yield('content')
            </div>
        </div>
        @include('partials.footer')
    </div>
</body>
</html>
