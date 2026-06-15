import * as bootstrap from 'bootstrap';
import Swal from 'sweetalert2';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.bootstrap = bootstrap;
window.Swal = Swal;
window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

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

    window.Echo.channel('queue')
        .listen('QueueUpdated', (e) => {
            const label = {
                called: 'dipanggil',
                in_progress: 'diproses',
                completed: 'selesai',
                cancelled: 'dibatalkan',
            }[e.action] ?? e.action;

            Toast.fire({
                icon: 'info',
                title: `Antrean ${e.queueNumber} ${label}`,
                timer: 4000,
            });

            document.dispatchEvent(new CustomEvent('queue-updated', { detail: e }));
        });
});
