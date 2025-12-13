export default defineNuxtConfig({
  ssr: false,

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
    baseURL: '/',
    buildAssetsDir: '_nuxt/'
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE_URL
    }
  }
})
