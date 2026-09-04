import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Outfit', {
                    weights: [300, 400, 500, 600, 700, 800],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        // ApexCharts is already loaded lazily via dynamic import() (app.js),
        // so it never blocks first paint. Its chunk is ~810 kB minified
        // (~228 kB gzip) by nature, so we set the limit above that to keep
        // the build output clean.
        chunkSizeWarningLimit: 900,
    },
});
