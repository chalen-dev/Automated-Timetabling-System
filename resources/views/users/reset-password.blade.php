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

                {{-- NEW: email is hidden and comes from the reset link --}}
                <input type="hidden" name="email" value="{{ old('email', $email ?? request('email')) }}"/>

                @if (session('success'))
                    <div class="w-full text-sm text-green-700 bg-green-50 border border-green-200 rounded px-3 py-2">
                        {{ session('success') }}
                    </div>
                @endif

                @error('email')
                <div class="w-full text-xs text-red-600">
                    {{ $message }}
                </div>
                @enderror

                <livewire:input.auth.password-text
                    label="NEW PASSWORD"
                    elementId="reset_password"
                    toggleId="toggleResetPassword"
                    type="password"
                    name="password"
                    placeholder="Enter new password"
                    isRequired
                />

                <livewire:input.auth.password-text
                    label="CONFIRM NEW PASSWORD"
                    elementId="reset_password_confirmation"
                    toggleId="toggleResetPasswordConfirmation"
                    type="password"
                    name="password_confirmation"
                    placeholder="Confirm new password"
                    isRequired
                />

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
