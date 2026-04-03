import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

export default defineConfig(({ mode }) => {
    // Load all .env variables (not just VITE_* prefixed ones) so we can read
    // CIVICORE_API_KEY inside this config file without baking it into the bundle.
    const env = loadEnv(mode, process.cwd(), '');

    const isProduction = mode === 'production';

    return {
        root: './',
        plugins: [
            react(),
            // Only use laravel-vite-plugin when building for production.
            // In dev we serve index.html directly via plain Vite.
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
            // Inject the API key as a <meta> tag into index.html at dev/build time.
            // In production the SPA is served by Blade (spa.blade.php) which injects
            // the key via a Blade expression. For the Vite dev server and static builds
            // we do the same thing here so the React fetch code works in both contexts.
            {
                name: 'inject-api-key',
                transformIndexHtml(html) {
                    const apiKey = env.CIVICORE_API_KEY || '';
                    return html.replace(
                        '</head>',
                        '  <meta name="api-key" content="' + apiKey + '">\n</head>'
                    );
                },
            },
        ],
        server: {
            port: 3000,
            // Proxy non-SPA routes to XAMPP Laravel so /login, /dashboard, /api/* etc work.
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
    };
});
