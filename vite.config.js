import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/tabulator.js',
                'resources/css/filament/admin/theme.css'
            ],
            refresh: true,
        }),
    ],
    build: {
        chunkSizeWarningLimit: 1500,
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost',
            port: 5173
        }
    }
});
