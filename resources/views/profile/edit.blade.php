@extends('app')

@section('title', 'Edit Profile')

@section('content')
    <div class="pt-16 pb-16 px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-[1200px] mx-auto flex flex-col p-10 gap-8">

            <!-- Header -->
            <div class="flex items-center gap-6">
                <img
                    id="profilePreview"
                    src="{{ $user->profile_photo_url }}"
                    class="w-20 h-20 rounded-full object-cover shadow-md"
                    style="width:5rem;height:5rem;"
                    alt="Profile Picture"
                />
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Edit Profile</h1>
                    <p class="text-sm text-gray-500">Update your info and upload a profile photo.</p>
                </div>
            </div>

            <!-- Form -->
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- GRID -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Profile Photo (full width) -->
                    <div class="lg:col-span-3">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">PROFILE PHOTO</label>
                        <input
                            id="profilePhotoInput"
                            type="file"
                            name="profile_photo"
                            accept="image/*"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white"
                        />
                        @error('profile_photo')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                        <div class="text-xs text-gray-500 mt-1">JPG/PNG/WEBP up to 2MB.</div>
                    </div>

                    <!-- First Name -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">FIRST NAME</label>
                        <input
                            type="text"
                            name="first_name"
                            value="{{ old('first_name', $user->first_name) }}"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                        />
                        @error('first_name')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">LAST NAME</label>
                        <input
                            type="text"
                            name="last_name"
                            value="{{ old('last_name', $user->last_name) }}"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                        />
                        @error('last_name')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Username -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">USERNAME</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                            required
                        />
                        @error('name')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email (2 columns) -->
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">EMAIL</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                            required
                        />
                        @error('email')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Passwords (same row) -->
                    <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">NEW PASSWORD (optional)</label>
                            <input
                                type="password"
                                name="password"
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                                placeholder="Leave blank to keep current"
                            />
                            @error('password')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">CONFIRM NEW PASSWORD</label>
                            <input
                                type="password"
                                name="password_confirmation"
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                            />
                        </div>
                    </div>

                </div>

                <!-- Buttons -->
                <div class="flex flex-row gap-4 mt-6 justify-between">
                    <button
                        type="submit"
                        class="px-8 py-3 rounded-[12px] bg-[#1e40af] text-white font-semibold hover:bg-[#1e3a8a] transition duration-500"
                    >
                        Save Changes
                    </button>

                    <a href="{{ route('profile.show') }}">
                        <button
                            type="button"
                            class="px-8 py-3 rounded-[12px] bg-[#aaa] text-white font-semibold hover:bg-[#828282] transition duration-500"
                        >
                            Cancel
                        </button>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const input = document.getElementById('profilePhotoInput');
            const img = document.getElementById('profilePreview');
            if (!input || !img) return;

            let lastObjectUrl = null;

            input.addEventListener('change', function () {
                const file = input.files && input.files[0];
                if (!file) return;
                if (!file.type || !file.type.startsWith('image/')) return;

                if (lastObjectUrl) URL.revokeObjectURL(lastObjectUrl);
                lastObjectUrl = URL.createObjectURL(file);
                img.src = lastObjectUrl;
            });
        })();
    </script>
@endsection
