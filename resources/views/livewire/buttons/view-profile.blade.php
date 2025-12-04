<a
    class="flex items-center gap-2 cursor-pointer p-3 rounded-[12px] hover:bg-[#5e0b0b] hover:text-[#ffffff] transition-transform duration-500"
    href="{{ route('profile.show') }}">
    <img
        src="{{ asset('pfp-placeholder.jpg') }}"
        class="w-8 h-8 rounded-full" alt="User Profile"
    >
    <span>{{ auth()->user()?->name ?? 'User' }}</span>
</a>
