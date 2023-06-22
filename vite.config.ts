import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import {watch} from "./resources/js/vite/watch-and-run";

/** @type {import('vite').UserConfig} */
export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        watch({
            pattern: "lang/**/*.php",
            command: "php artisan publish:lang-js",
        }),
    ]
});
