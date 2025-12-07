import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    host: '0.0.0.0',
    port: 3000,
    watch: {
      usePolling: true,
    },
    // AJOUTEZ CETTE SECTION PROXY :
    proxy: {
      '/api': {
        target: 'http://backend:8000', // ou 'http://localhost:8000'
        changeOrigin: true,
        secure: false,
      },
      '/sanctum': {
        target: 'http://backend:8000',
        changeOrigin: true,
        secure: false,
      },
      '/storage': {
        target: 'http://backend:8000',
        changeOrigin: true,
      },
    },
  },
})