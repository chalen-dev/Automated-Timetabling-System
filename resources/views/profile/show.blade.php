@extends('app')

@section('title', 'My Profile')

@section('content')
    <div class="flex justify-center pt-16 pb-16 px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-[1000px] flex flex-col md:flex-row p-10 gap-10 items-center md:items-start">

            <!-- Profile Picture -->
            <div class="flex flex-col items-center md:items-start gap-4">
                <img src="{{ $user->profile_photo_url }}" class="w-32 h-32 rounded-full object-cover shadow-md" alt="Profile Picture">
                <div class="text-center md:text-left">
                    <h1 class="text-3xl font-bold">{{ $user->name }}</h1>
                    <p class="text-gray-500 text-lg">{{ $user->email }}</p>
                </div>
            </div>

            <!-- Profile Info -->
            <div class="flex-1 flex flex-col gap-4 w-full">
                <div class="flex justify-between p-4 bg-gray-50 rounded-lg">
                    <span class="font-semibold text-gray-700">Email</span>
                    <span class="text-gray-900">{{ $user->masked_email }}</span>
                </div>

                <div class="flex justify-between p-4 bg-gray-50 rounded-lg">
                    <span class="font-semibold text-gray-700">Password</span>
                    <span class="text-gray-900">{{ $user->masked_password }}</span>
                </div>

                <!-- Buttons -->
                <div class="flex flex-row gap-6 mt-6">
                    <a href="{{ route('profile.edit') }}">
                        <button class="px-8 py-3 rounded-[12px] bg-[#1e40af] text-white font-semibold hover:bg-[#1e3a8a] transition duration-500">
                            Edit Profile
                        </button>
                    </a>

                    <a href="{{ route('timetables.index') }}">
                        <button class="px-8 py-3 rounded-[12px] bg-[#aaa] text-white font-semibold hover:bg-[#828282] transition duration-500">
                            Back
                        </button>
                    </a>
                </div>
            </div>

        </div>
    </div>
@endsection
