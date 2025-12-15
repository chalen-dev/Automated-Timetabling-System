@extends('app')

@section('title', 'Reset Password')

@section('content')
    <div class="flex flex-row items-center justify-center w-100vh h-100% content-around gap-10">
        <div class="flex flex-col max-w-md text-center md:text-left mr-64">
            <h1 class="text-4xl font-extrabold mb-4 text-white drop-shadow">
                SET A NEW <span class="text-[#fbcc15]">PASSWORD</span>
            </h1>
            <p class="text-lg text-white/90 leading-relaxed">
                Choose a strong password. After resetting, you can login again.
            </p>
        </div>

        <div class="mt-35 mb-45">
            <form action="{{ route('password.update') }}" method="POST"
                  class="flex flex-col w-90 content-between rounded-xl items-center p-5 gap-3 bg-white shadow-2xl">
                @csrf

                <h1 class="font-bold p-1">Reset Password</h1>

                <input type="hidden" name="token" value="{{ $token }}"/>

                @if (session('success'))
                    <div class="w-full text-sm text-green-700 bg-green-50 border border-green-200 rounded px-3 py-2">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="w-full">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                        EMAIL
                    </label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                        placeholder="your.email@domain.com"
                        required
                    />
                    @error('email')
                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="w-full">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                        NEW PASSWORD
                    </label>
                    <input
                        type="password"
                        name="password"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                        placeholder="Enter new password"
                        required
                    />
                    @error('password')
                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="w-full">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                        CONFIRM NEW PASSWORD
                    </label>
                    <input
                        type="password"
                        name="password_confirmation"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                        placeholder="Confirm new password"
                        required
                    />
                </div>

                <button type="submit"
                        class="bg-[#fbcc15] text-black px-4 py-2 rounded font-bold w-full hover:cursor-pointer">
                    Reset Password ➝
                </button>

                <div class="text-sm">
                    <a href="{{ route('login.form') }}" class="underline hover:font-bold text-[#5E0B0B]">
                        Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
