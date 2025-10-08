<nav class="z-2 padding-12 w-full">
    <div class="flex gap-4 justify-between pl-20 pr-20 z-50">
                @if (request()->routeIs('home') || request()->routeIs('login.form'))
                    <div class="flex flex-row gap-4 items-center">
                        <img src="{{asset('umtc_logo.png')}}" class="w-15 h-15" alt="UMTC Logo" >
                        <h2 class="font-bold text-[#ffffff]">FaculTime</h2>
                    </div>
                    <a href="{{ route('register.form') }}" class="content-center">
                            <button type=button class="bg-white text-[#5E0B0B] px-4 py-2 rounded-lg shadow h-12 flex items-center cursor-pointer">
                                <span>Sign Up</span>
                            </button>
                    </a>
                @elseif (request()->routeIs('register.form'))
                    <div class="flex flex-row gap-4 items-center">
                        <img src="{{asset('umtc_logo.png')}}" class="w-15 h-15" alt="UMTC Logo" >
                        <h2 class="font-bold text-[#ffffff]">FaculTime</h2>
                    </div>
                    <a href="{{ route('login.form') }}" class="content-center">
                        <button type=button class="bg-white text-[#5E0B0B] px-4 py-2 rounded-lg shadow h-12 flex items-center cursor-pointer">
                            <span>Sign In</span>
                        </button>
                    </a>
                @endif
    </div>
</nav>

