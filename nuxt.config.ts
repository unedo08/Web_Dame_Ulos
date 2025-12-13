export default defineNuxtConfig({
  ssr: false,

  app: {
    baseURL: '/dame-ulos/',
    buildAssetsDir: '_nuxt/',
  },

  nitro: {
    preset: 'static',
    prerender: {
      routes: ['/', '/login'], // ⬅️ PENTING
    },
  },

  plugins: ['~/plugins/pinia.ts'],

  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE_URL,
    },
  },
})
