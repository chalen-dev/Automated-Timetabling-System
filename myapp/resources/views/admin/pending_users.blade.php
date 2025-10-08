@extends('app')

@section('title', 'Manage Users')

@section('content')
    <div class="flex flex-col h-[calc(100vh-55px)] w-full p-6 space-y-6 bg-gray-100">

        <h1 class="text-2xl text-gray-800 font-semibold mb-5">Manage Users</h1>

        @if(session('success'))
            <p class="mb-3 text-green-600 font-semibold">{{ session('success') }}</p>
        @endif

        @php
            // Group users by role
            $groupedUsers = $pendingUsers->groupBy('role');
        @endphp

        {{-- === PENDING USERS SECTION === --}}
        <div class="bg-white shadow-lg rounded-2xl p-5">
            <h2 class="text-gray-800 text-xl font-semibold mb-4">Pending Users</h2>

            @forelse ($groupedUsers['pending'] ?? [] as $user)
                <div class="flex flex-col md:flex-row items-center justify-between p-4 mb-3 bg-gray-50 rounded-xl shadow hover:shadow-md transition-all">
                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                        <p class="font-bold text-gray-800">{{ $user->name }}</p>
                        <p class="text-gray-600 text-sm">{{ $user->email }}</p>
                        <p class="text-gray-500 text-xs font-semibold capitalize">{{ $user->role }}</p>
                    </div>

                    <div class="flex gap-3 mt-3 md:mt-0">
                        <!-- Authorize Button -->
                        <form method="POST" action="{{ route('admin.toggle_authorize', $user->id) }}">
                            @csrf
                            <button type="submit"
                                    class="bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-2 rounded-lg transition-all">
                                Authorize
                            </button>
                        </form>

                        <!-- Decline Button -->
                        <x-buttons.delete
                            :action="'admin.decline_user'"
                            :params="$user->id"
                            :item_name="$user->name"
                            btnType="normal"
                            class="px-4 py-2 font-semibold bg-red-500 hover:bg-red-600 text-white"
                        />
                    </div>
                </div>
            @empty
                <p class="text-gray-500">No pending users found.</p>
            @endforelse
        </div>

        {{-- === AUTHORIZED USERS SECTION === --}}
        <div class="bg-white shadow-lg rounded-2xl p-5">
            <h2 class="text-gray-800 text-xl font-semibold mb-4">Authorized Users</h2>

            @forelse ($groupedUsers['authorized'] ?? [] as $user)
                <div class="flex flex-col md:flex-row items-center justify-between p-4 mb-3 bg-gray-50 rounded-xl shadow hover:shadow-md transition-all">
                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                        <p class="font-bold text-gray-800">{{ $user->name }}</p>
                        <p class="text-gray-600 text-sm">{{ $user->email }}</p>
                        <p class="text-green-600 text-xs font-semibold capitalize">{{ $user->role }}</p>
                    </div>

                    <div class="flex gap-3 mt-3 md:mt-0">
                        <!-- Toggle Authorize Button -->
                        <form method="POST" action="{{ route('admin.toggle_authorize', $user->id) }}">
                            @csrf
                            <button type="submit"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-4 py-2 rounded-lg transition-all">
                                Set as Not Authorized
                            </button>
                        </form>

                        <!-- Decline Button -->
                        <x-buttons.delete
                            :action="'admin.decline_user'"
                            :params="$user->id"
                            :item_name="$user->name"
                            btnType="normal"
                            class="px-4 py-2 font-semibold bg-red-500 hover:bg-red-600 text-white"
                        />
                    </div>
                </div>
            @empty
                <p class="text-gray-500">No authorized users found.</p>
            @endforelse
        </div>

    </div>
@endsection
