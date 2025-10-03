<nav class="top-0 z-50 w-screen">
    <ul class="flex gap-4 justify-between pl-10 pr-10 z-50 w-full">
        <li>
            <img src="{{asset('umtc_logo.png')}}" class="w-15 h-15" alt="UMTC Logo" >
        </li>
        <button class="flex gap-4 justify-center items-center w-20 bg-white rounded-xl">
            @if (request()->routeIs('home') || request()->routeIs('login.form'))
                <a href="{{ route('register.form') }}">Sign Up</a>
            @elseif (request()->routeIs('register.form'))
                <a href="{{ route('login.form') }}">Sign in</a>
            @endif
        </button>
    </ul>
</nav>

