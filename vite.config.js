import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        root: './',

        plugins: [
            react(),

            // WAJIB selalu aktif (ini kunci fix manifest)
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.jsx'],
                refresh: true,
            }),

            tailwindcss(),

            // Inject API key ke index.html (optional, punyamu sudah bagus)
            {
                name: 'inject-api-key',
                transformIndexHtml(html) {
                    const apiKey = env.CIVICORE_API_KEY || '';
                    return html.replace(
                        '</head>',
                        `  <meta name="api-key" content="${apiKey}">\n</head>`
                    );
                },
            },
        ],

        server: {
            port: 3000,
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

            // 🔥 INI KUNCI AGAR MANIFEST DI ROOT (BUKAN .vite/)
            manifest: 'manifest.json',

            emptyOutDir: true,

            rollupOptions: {
                input: {
                    app: 'resources/js/app.jsx',
                    css: 'resources/css/app.css',
                },
            },
        },
    };
});
