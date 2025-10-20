@php
    use SweetAlert2\Laravel\Swal;
@endphp

@props([
    'action',
    'params',
    'item_name',
    'btnType' => 'normal',
    'class' => '',
])

<form action="{{ route($action, $params) }}" method="POST" class="flex items-center justify-center delete-form">
    @csrf
    @method('DELETE')

    <button
        type="submit"
        data-item-name="{{ $item_name }}"
        class="delete-btn flex items-center justify-center rounded cursor-pointer transition-all duration-150 {{ $class }}"
    >

    @if($btnType === 'normal')
            <p>Delete</p>
        @elseif($btnType === 'icon')
            <i class="bi bi-trash"></i>
        @endif
    </button>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-btn').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = button.closest('form');
                const itemName = button.dataset.itemName;

                Swal::fire({
                    title: 'Are you sure?',
                    text: `You are about to delete "${itemName}". This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.requestSubmit(); // ✅ safer than form.submit()
                    }
                });
            });
        });
    });
</script>
