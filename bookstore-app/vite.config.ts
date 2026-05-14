import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

const backendProxy = {
  target: 'http://127.0.0.1:8001',
  changeOrigin: true,
}

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    proxy: {
      '/backend': backendProxy,
      '/api': backendProxy,
    },
  },
  preview: {
    proxy: {
      '/backend': backendProxy,
      '/api': backendProxy,
    },
  },
})
