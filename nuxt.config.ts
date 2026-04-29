import tailwindcss from '@tailwindcss/vite'

export default defineNuxtConfig({
  ssr: false,

  css: [
    '~/assets/css/tailwind.css',
    '~/assets/css/print.css'
  ],

  experimental: {
    payloadExtraction: false,
  },

  vite: {
    plugins: [tailwindcss()],
    build: {
      sourcemap: false,
      rollupOptions: {
        output: {
          manualChunks: {
            'vendor-chart': ['chart.js', 'vue-chartjs', 'chartjs-plugin-datalabels'],
            'vendor-swal': ['sweetalert2'],
            'vendor-dayjs': ['dayjs'],
          }
        }
      }
    },
    optimizeDeps: {
      include: [
        'chart.js',
        'vue-chartjs',
        'chartjs-plugin-datalabels',
        'sweetalert2',
        'dayjs'
      ]
    }
  },

  nitro: {
    preset: 'static'
  },

  plugins: ['~/plugins/pinia.ts'],

  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE_URL
    }
  },

  compatibilityDate: '2025-04-02'
})
