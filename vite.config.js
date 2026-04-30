import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

const isCodespaces = process.env.CODESPACE_NAME;

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0', // Bind to all interfaces
        hmr: {
            protocol: 'wss', // Codespaces uses HTTPS, so HMR must use secure WebSocket
            host: isCodespaces 
                ? `${process.env.CODESPACE_NAME}-5173.${process.env.GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}` 
                : undefined,
        },
        origin: isCodespaces 
            ? `https://${process.env.CODESPACE_NAME}-5173.${process.env.GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}` 
            : undefined,
    },
});
