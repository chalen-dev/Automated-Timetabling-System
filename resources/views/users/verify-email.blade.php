@extends('app')

@section('title', 'Verify Email')

@section('content')
    <div class="flex flex-row items-center justify-center w-100vh h-100% content-around gap-10">
        <div class="flex flex-col max-w-md text-center md:text-left mr-64">
            <h1 class="text-4xl font-extrabold mb-4 text-white drop-shadow">
                VERIFY YOUR <span class="text-[#fbcc15]">EMAIL</span>
            </h1>
            <p class="text-lg text-white/90 leading-relaxed">
                We sent a verification link to your email address. Please click it to continue.
            </p>
        </div>

        <div class="mt-35 mb-45">
            <div class="flex flex-col w-90 content-between rounded-xl items-center p-5 gap-3 bg-white shadow-2xl">
                <h1 class="font-bold p-1">Email Verification Required</h1>

                @if (session('success'))
                    <div class="w-full text-sm text-green-700 bg-green-50 border border-green-200 rounded px-3 py-2">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="w-full text-sm text-gray-700">
                    <div class="mb-2">
                        If you didn’t receive the email, you can request another verification link below.
                    </div>

                    @if (auth()->check())
                        <div class="text-xs text-gray-500">
                            Logged in as: <span class="font-semibold">{{ auth()->user()->email }}</span>
                        </div>
                    @endif
                </div>

                <form action="{{ route('verification.send') }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit"
                            class="bg-[#fbcc15] text-black px-4 py-2 rounded font-bold w-full hover:cursor-pointer">
                        Resend Verification Email ➝
                    </button>
                </form>

                <form action="{{ route('logout') }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit"
                            class="bg-gray-200 text-gray-800 px-4 py-2 rounded font-bold w-full hover:cursor-pointer">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
