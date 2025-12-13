// nuxt.config.ts
export default defineNuxtConfig({
  ssr: false,

  app: {
    // karena deploy di:
    // domains/databasedameulos.com/public_html/dame-ulos
    baseURL: '/dame-ulos/',
    buildAssetsDir: '_nuxt/',
  },

  nitro: {
    preset: 'static'
  },

  css: [
    '~/assets/css/tailwind.css',
    '~/assets/css/print.css',
  ],

  vite: {
    build: {
      chunkSizeWarningLimit: 1600
    }
  }
})
