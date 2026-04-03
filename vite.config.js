import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

const isProduction = process.env.NODE_ENV === 'production';

export default defineConfig({
    root: './',
    plugins: [
        react(),
        // Only use laravel-vite-plugin when building for production
        // In dev, we serve index.html directly with plain Vite
        ...(isProduction
            ? [
                laravel({
                    input: ['resources/css/app.css', 'resources/js/app.jsx'],
                    refresh: true,
                }),
            ]
            : []
        ),
        tailwindcss(),
    ],
    server: {
        port: 3000,
        // Proxy non-SPA routes to XAMPP Laravel so /login, /dashboard etc work
        proxy: {
            '^/(?!$|admin$|admin/|resources/|@vite|@react-refresh|node_modules/).*': {
                target: 'http://localhost',
                changeOrigin: true,
                rewrite: (path) => '/civicore/public' + path,
            },
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        outDir: 'public/build',
        manifest: true,
        rollupOptions: {
            input: {
                app: 'resources/js/app.jsx',
                css: 'resources/css/app.css',
            },
        },
    },
});
