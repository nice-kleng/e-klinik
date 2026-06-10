import 'bootstrap';
import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', function () {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });

    const flash = window.flash ?? {};
    if (flash.success) {
        Toast.fire({ icon: 'success', title: flash.success });
    }
    if (flash.error) {
        Toast.fire({ icon: 'error', title: flash.error });
    }
    if (flash.warning) {
        Toast.fire({ icon: 'warning', title: flash.warning });
    }
    if (flash.info) {
        Toast.fire({ icon: 'info', title: flash.info });
    }

    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: this.dataset.confirm,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
});
