import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react({
    include: "**/*.{jsx,js}",
  })],
  build: {
    // Enable minification
    minify: 'terser',
    // Enable source maps for debugging
    sourcemap: false,
    // Optimize chunk splitting
    rollupOptions: {
      output: {
        manualChunks: {
          // Vendor chunk for large dependencies
          vendor: ['react', 'react-dom', 'react-router-dom'],
          // UI chunk for UI components
          ui: ['@headlessui/react', '@heroicons/react'],
          // Query chunk for data fetching
          query: ['@tanstack/react-query', 'axios'],
          // Forms chunk
          forms: ['formik']
        }
      }
    },
    // Increase chunk size warning limit
    chunkSizeWarningLimit: 1000
  },
  // Performance optimizations
  optimizeDeps: {
    include: [
      'react',
      'react-dom',
      'react-router-dom',
      '@tanstack/react-query',
      'axios',
      'formik'
    ]
  },
  // Server configuration
  server: {
    // Enable HMR
    hmr: true,
    // Open browser on start
    open: false
  }
})
