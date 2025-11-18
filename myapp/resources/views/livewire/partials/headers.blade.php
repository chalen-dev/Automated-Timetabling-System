<div>

    @guest

        <!-- 1. Guest Header -->
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

    @endguest

    @auth
        <!--If request is from the timetabling routes (timetables/{nested resource}/{more nested resoucrce, etc.}-->
        @if(Str::is('timetables.*.*', $currentRouteName))

            <!-- 2. Auth Header (Timetabling Section) -->
            <nav class="fixed mb-4 w-[97.1%] z-2 flex justify-center items-center content-center h-18 shadow-2xl rounded-2xl">
                <div class="h-18 w-full flex items-center justify-between pl-9 pr-9 pt-6 pb-6 bg-white rounded-2xl">
                    <div>
                        <button class="flex items-center justify-center w-15 h-15">
                            <a href="{{route('timetables.index')}}">
                                <i class="bi bi-arrow-left text-2xl font-bold"></i>
                            </a>
                        </button>
                    </div>
                    <div class="flex flex-col text-center box-fit">
                        <h1 class="font-bold text-[18px]">Timetable View</h1>
                        <p>{{$timetable->timetable_name}} {{$timetable->semester}} semester ({{$timetable->academic_year}})</p>
                    </div>
                    <div>
                        <img src="{{ asset('umtc_logo.png') }}" class="w-15 h-15" alt="UMTC Logo"/>
                    </div>
                </div>
            </nav>

        @else <!-- Else, use the default header for the records section -->

            <!-- 3. Auth Header (Records Section) -->
            <nav x-data class="fixed mb-4 z-2 w-[97.1%] flex justify-center items-center content-center h-18 shadow-2xl rounded-2xl">
                <div class="h-18 w-full flex items-center justify-between pl-9 pr-9 pt-6 pb-6 bg-white rounded-2xl">
                    <div class="flex items-center gap-4">
                        <!-- HAMBURGER BUTTON -->
                        <button
                            @click="$dispatch('toggle-sidebar')" {{-- Alpine Implementation --}}
                            class="text-2xl cursor-pointer"
                        >
                            ☰
                        </button>
                        <h2 class="font-bold text-[#5E0B0B]">FaculTime</h2>
                    </div>

                    <div class="flex items-center gap-10 justify-between">
                        <div class="flex items-center gap-2">
                            <img
                                src="{{ asset('pfp-placeholder.jpg') }}"
                                class="w-8 h-8 rounded-full" alt="User Profile"
                            >
                            <span>{{ auth()->user()?->name ?? 'User' }}</span>
                        </div>

                        <livewire:buttons.log-out/>
                    </div>
                </div>
            </nav>


        @endif

    @endauth

</div>
