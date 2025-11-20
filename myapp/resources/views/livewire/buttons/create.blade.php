<div>
    @if($route)
        <a
            href="{{ route($route) }}"
            class="
                {{ $class ?: 'bg-yellow-500 text-[#5e0b0b] px-4 py-2 rounded-lg font-semibold shadow hover:bg-yellow-600 active:bg-yellow-700 transition-all duration-150'}}
            "
        >
                {{ $text }}
        </a>
    @endif
    @if($submit)
        <button
            type="submit"
            class="
             {{ $class ?: 'pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#5e0b0b] text-[#fff] cursor-pointer font-[600]' }}
             "
        >
            {{ $text }}
        </button>
    @endif
</div>

