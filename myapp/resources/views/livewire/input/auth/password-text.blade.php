<div class="w-full gap-1 mb-3">
    <label>
        @if($isRequired)
            {{$label}} *
        @endif
        {{$label}}

    </label>
    <div class="flex items-center rounded-lg border border-gray-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-black focus-within:border-transparent transition">
        <input class ="flex-4 px-4 py-2 text-sm w-full focus:outline-none" id = "{{$elementId}}" type="{{$type}}" name="{{$name}}" placeholder="{{$placeholder}}" value="{{old($name, $value)}}">
        <button class = 'flex-0.1 ml-2 bg-white text-gray-700 px-4 py-1 rounded' type="button" id="{{ $toggleId }}">
            <i class="bi bi-eye-slash hover:cursor-pointer"></i>
        </button>
    </div>

    @error($name)
    <span class="!text-red-500">{{$message}}</span>
    @enderror
</div>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.querySelector('#{{$elementId}}');
            const toggleBtn = document.querySelector('#{{$toggleId}}');

            if (passwordInput && toggleBtn){

                toggleBtn.addEventListener('click', function() {

                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        toggleBtn.innerHTML = '<i class="bi bi-eye hover:cursor-pointer"></i>';
                    }

                    else {
                        passwordInput.type = 'password';
                        toggleBtn.innerHTML = '<i class="bi bi-eye-slash hover:cursor-pointer"></i>';
                    }

                })
            }
            console.log(passwordInput)
            console.log(toggleBtn)
        });
    </script>
@endpush
