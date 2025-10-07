@props([
    'action',
    'name' => 'search',
    'placeholder' => 'Search...',
    'buttonText' => 'Search'
])

<form method="GET" action="{{ $action }}" class="mb-4 flex gap-2 items-center justify-center">
    <input
        type="text"
        name="{{ $name ?? 'search' }}"
        value="{{ request($name ?? 'search') }}"
        placeholder="{{ $placeholder ?? 'Search...' }}"
        class="border rounded px-2 py-1 w-100 bg-white bg-opacity-50"
    >
    <button type="submit" class="bg-white text-[maroon] px-3 py-1 rounded hover:cursor-pointer">
        {{ $buttonText ?? 'Search' }}
    </button>

    @if(request($name ?? 'search'))
        <a href="{{ $action }}" class="bg-gray-300 px-3 py-1 rounded">Clear</a>
    @endif
</form>
