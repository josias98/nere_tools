import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const pfxPath = path.resolve(__dirname, 'storage/local-certs/localhost.pfx');
const https = fs.existsSync(pfxPath)
    ? { pfx: fs.readFileSync(pfxPath), passphrase: 'nere-tools-local-dev' }
    : undefined;

export default defineConfig({
    resolve: {
        preserveSymlinks: true,
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        https,
        hmr: https ? { host: 'localhost', protocol: 'wss' } : undefined,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
