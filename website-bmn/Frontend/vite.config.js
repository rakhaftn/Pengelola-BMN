import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const backendPath = path.resolve(__dirname, '../Backend');

export default defineConfig({
    plugins: [
        laravel({
            input: ['css/app.css', 'js/app.jsx'],
            publicDirectory: path.join(backendPath, 'public'),
            buildDirectory: 'build',
            hotFile: path.join(backendPath, 'public/hot'),
            refresh: [
                path.join(backendPath, 'resources/views/**'),
                path.join(backendPath, 'routes/**'),
            ],
        }),
        tailwindcss(),
        react(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'js'),
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
