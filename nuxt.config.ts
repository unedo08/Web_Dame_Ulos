export default defineNuxtConfig({
  ssr: false, // ⬅️ PENTING untuk sessionStorage

  modules: [
    '@nuxtjs/tailwindcss',
    'shadcn-nuxt'
  ],

  css: [
    '~/assets/css/tailwind.css',
    '~/assets/css/print.css'
  ],

  nitro: {
    preset: 'static'
  },

  app: {
    baseURL: '/',          // ⬅️ HOSTINGER ROOT
    buildAssetsDir: '_nuxt/'
  },

  router: {
    middleware: ['auth']
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE_URL
    }
  }
})
