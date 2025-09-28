@props(['label', 'type', 'elementId', 'placeholder', 'name', 'value' => '' , 'toggleId' => 'toggleBtn'])
<div>
    <label>{{$label}}</label>
    <div class="flex items-center">
        <input id = "{{$elementId}}" type="{{$type}}" name="{{$name}}" placeholder="{{$placeholder}}" value="{{old($name, $value)}}">
        <button type="button" id="{{$toggleId}}" class="bg-grey-500 text-white px-4 py-2 rounded">Show</button>
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
                            toggleBtn.textContent = 'Hide';
                        }

                        else {
                            passwordInput.type = 'password';
                            toggleBtn.textContent = 'Show';
                        }

                    })
                }
                console.log(passwordInput)
                console.log(toggleBtn)
            });
        </script>
    @endpush

