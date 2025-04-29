import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/theme.css',
                'resources/css/app.css',
                'resources/css/home.css',
                'resources/css/frontend.css',
                'resources/css/lightbox.css',
                'resources/css/filament/admin/theme.css',
                'resources/js/app.js',
                'resources/js/carousel.js',
            ],
            refresh: [`resources/views/**/*`],
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
    },
});
