import tailwindcss from '@tailwindcss/vite'

export default defineNuxtConfig({

  app: {
    baseURL: '/dame-ulos/',
    buildAssetsDir: '_nuxt/',
    cdnURL: '/dame-ulos/',
    head: {
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/dame-ulos/favicon.ico' }
      ]
    }
  },

  router: {
    middleware: ['auth'],
    options: {
      base: '/dame-ulos/'
    }
  },

  imports: {
    dirs: [] // FIX shadcn auto-import issue in subfolder deployment
  },

  modules: ['shadcn-nuxt'],

  shadcn: {
    componentDir: './components/ui'
  },

  css: [
    '~/assets/css/tailwind.css',
    '~/assets/css/print.css'
  ],

  vite: {
    plugins: [
      tailwindcss()
    ],
    build: {
      rollupOptions: {
        output: {
          // memastikan semua chunk memakai prefix baseURL
          assetFileNames: '_nuxt/[hash][extname]',
          chunkFileNames: '_nuxt/[hash].js',
        }
      }
    }
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE_URL
    }
  },

  compatibilityDate: '2025-04-02'
})
