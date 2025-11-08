import Echo from 'laravel-echo';

import Pusher from 'pusher-js'; // <-- 1. Impor 'pusher-js'
window.Pusher = Pusher; // <-- 2. Penting: Setel di 'window'

window.Echo = new Echo({
    broadcaster: 'pusher', // <-- 3. Ganti driver menjadi 'pusher'
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'], // <-- 4. Aktifkan transport
});