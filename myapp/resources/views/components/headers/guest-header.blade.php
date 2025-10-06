<nav class="relative z-2 pb-4 padding-12 w-full">
    <div class="flex gap-4 justify-between pl-20 pr-20 z-50">
            <img src="{{asset('umtc_logo.png')}}" class="w-15 h-15" alt="UMTC Logo" >
        <button type=button class="bg-white text-[#5E0B0B] px-4 py-2 rounded-lg shadow h-12 flex items-center">
            @if (request()->routeIs('home') || request()->routeIs('login.form'))
                <a href="{{ route('register.form') }}">Sign Up</a>
            @elseif (request()->routeIs('register.form'))
                <a href="{{ route('login.form') }}">Sign in</a>
            @endif
        </button>
    </div>
</nav>

