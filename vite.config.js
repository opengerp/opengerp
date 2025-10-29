import { defineConfig } from 'vite';
export default defineConfig({
    build: {
        outDir: 'public/assets',
        manifest: true,
        rollupOptions: { input: 'frontend/js/app.js' }
    }
});