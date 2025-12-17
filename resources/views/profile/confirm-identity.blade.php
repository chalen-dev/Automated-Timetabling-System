@extends('app')

@section('title', 'Confirm Identity')

@section('content')
    <div class="flex justify-center pt-20 px-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-8">

            <h2 class="text-2xl font-bold mb-6 text-center">
                Confirm Your Identity
            </h2>

            <form method="POST" action="{{ route('profile.confirm.verify') }}">
                @csrf

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Password
                    </label>

                    <input type="password"
                           name="password"
                           class="w-full px-4 py-3 rounded-lg border focus:ring-2 focus:ring-blue-600"
                           required>

                    @error('password')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-between">
                    <a href="{{ route('profile.show') }}"
                       class="px-6 py-3 rounded-lg bg-gray-400 text-white font-semibold hover:bg-gray-500 transition">
                        Cancel
                    </a>

                    <button type="submit"
                            class="px-6 py-3 rounded-lg bg-blue-700 text-white font-semibold hover:bg-blue-800 transition">
                        Continue
                    </button>
                </div>
            </form>

        </div>
    </div>
@endsection
