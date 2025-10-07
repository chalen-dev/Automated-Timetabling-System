<nav class="relative z-2 pb-4 padding-12 w-full">
    <div class="flex gap-4 justify-between pl-20 pr-20 z-50">
                @if (request()->routeIs('home') || request()->routeIs('login.form'))
            <img src="{{asset('umtc_logo.png')}}" class="w-15 h-15" alt="UMTC Logo" >
                    <a href="{{ route('register.form') }}">
                        <button type=button class="bg-white text-[#5E0B0B] px-4 py-2 rounded-lg shadow h-12 flex items-center cursor-pointer">
                            <span>Sign Up</span>
                        </button>
                    </a>
                @elseif (request()->routeIs('register.form'))
            <img src="{{asset('umtc_logo.png')}}" class="w-15 h-15" alt="UMTC Logo" >
                    <a href="{{ route('login.form') }}">
                        <button type=button class="bg-white text-[#5E0B0B] px-4 py-2 rounded-lg shadow h-12 flex items-center cursor-pointer">
                            <span>Sign in</span>
                        </button>
                    </a>
                @endif
    </div>
</nav>

