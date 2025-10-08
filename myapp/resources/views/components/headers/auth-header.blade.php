<nav class="fixed mb-4 z-2 w-[97.1%] flex justify-center items-center content-center h-18 shadow-2xl rounded-2xl">
    <div class="h-18 w-full flex items-center justify-between pl-9 pr-9 pt-6 pb-6 bg-white rounded-2xl">
        <div class="flex items-center gap-4">
            <!-- HAMBURGER BUTTON -->
            <button class = "text-2xl" @click="$store.sidebar.open = true">
                ☰
            </button>
            <h2 class="font-bold text-[#5E0B0B]">FaculTime</h2>
        </div>

        <div class="flex items-center gap-10 justify-between">
            <div class="flex items-center gap-2">
                <img src="{{ asset('pfp-placeholder.jpg') }}" class="w-8 h-8 rounded-full" alt="User Profile">
                <span>{{ auth()->user()?->name ?? 'User' }}</span>
            </div>

            <form action="{{ route('logout') }}" method="post">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </div>
    </div>
</nav>
