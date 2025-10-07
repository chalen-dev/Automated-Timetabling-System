@extends('app')

@section('title', 'Pending Users')

@section('content')
    <div class="flex flex-col h-[calc(100vh-55px)] w-full p-3">
        <h1 class="text-16px text-[#ffffff] font-semibold space-1px mb-5">Pending Users</h1>

        @if(session('success'))
            <p class="mb-3 text-green-500 font-semibold">{{ session('success') }}</p>
        @endif

        <ul class="flex flex-wrap gap-7">
            @foreach($pendingUsers as $user)
                <li class="flex flex-col justify-between h-50 w-75 p-5 bg-white shadow-2xl rounded-2xl hover:-translate-y-[6px] hover:shadow-[0_8px_18px_rgba(0,0,0,0.08)] transition-all duration-300 ease-in-out">
                    <div class="flex flex-col items-center pb-3">
                        <p class="font-bold">{{ $user->name }}</p>
                        <p class="text-sm">{{ $user->email }}</p>
                    </div>
                    <div class="flex justify-center">
                        <form method="POST" action="{{ route('admin.approve_user', $user->id) }}">
                            @csrf
                            <button type="submit"
                                    class="bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-2 rounded-lg">
                                Approve
                            </button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endsection
