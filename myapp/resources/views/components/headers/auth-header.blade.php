<nav class="fixed top-0 left-0 right-0 pt-8 pb-8 pl-12 pr-12 z-50 w-screen flex justify-center items-center content-center
 h-20">
    <div class="w-full h-16 flex items-center justify-between pl-9 pr-9">
        <div class="flex items-center gap-4">
            <!-- HAMBURGER BUTTON -->
            <button class = "text-2xl" @click="$store.sidebar.open = true">
                ☰
            </button>
            <h2>FaculTime</h2>
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
