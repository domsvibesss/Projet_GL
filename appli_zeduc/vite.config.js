import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/main.js',
                'resources/js/menu.js',
                'resources/js/plat-du-jour.js',
                'resources/js/panier-page.js',
                'resources/js/finalisation.js',
                'resources/js/historique.js',
                'resources/js/fidelite-page.js',
                'resources/js/reclamations-page.js',
                'resources/js/connexion.js',
                'resources/js/inscription.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
