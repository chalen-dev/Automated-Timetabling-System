<form action="{{ route('logout') }}" method="POST">
    @csrf
    <button
        type="submit"
        class=" log-out-btn cursor-pointer p-3 rounded-[12px] hover:bg-[#5e0b0b] hover:text-[#ffffff] transition-transform duration-500"
    >
        Logout
    </button>
</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.log-out-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const form = button.closest('form');

                Swal.fire({
                    title: 'Log Out',
                    text: 'Are you sure you want to log out?',
                    icon: 'question',
                    showCancelButton: true,
                    showConfirmButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                })
                    .then(result => {
                        if (result.isConfirmed) {
                            form.requestSubmit();
                        }
                    });
            });
        })
    })
</script>
