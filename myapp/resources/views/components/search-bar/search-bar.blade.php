@props([
    'action',
    'name' => 'search',
    'placeholder' => 'Search...',
    'buttonText' => 'Search'
])

<form method="GET" action="{{ $action }}" class="mb-4 flex gap-2">
    <input
        type="text"
        name="{{ $name ?? 'search' }}"
        value="{{ request($name ?? 'search') }}"
        placeholder="{{ $placeholder ?? 'Search...' }}"
        class="border rounded px-2 py-1 w-64"
    >
    <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded">
        {{ $buttonText ?? 'Search' }}
    </button>

    @if(request($name ?? 'search'))
        <a href="{{ $action }}" class="bg-gray-300 px-3 py-1 rounded">Clear</a>
    @endif
</form>
