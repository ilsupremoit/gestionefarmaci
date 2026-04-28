import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/auth/first-acess.css',
                'resources/css/auth/forgot-password.css',
                'resources/css/auth/login.css',
                'resources/css/auth/reset-password.css',
                'resources/css/medico/dashboard.css',
                'resources/css/medico/dispositivo-show.css',
                'resources/css/medico/createPaziente.css',
                'resources/css/medico/infoPaziente.css',
                'resources/css/medico/MieiPazienti.css',
                'resources/css/medico/notificheMedico.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
