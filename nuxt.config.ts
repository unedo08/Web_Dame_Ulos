import tailwindcss from '@tailwindcss/vite'

export default defineNuxtConfig({
  ssr: false,

  app: {
    baseURL: '/dame-ulos/',
    buildAssetsDir: '_nuxt/',
  },

  modules: ['shadcn-nuxt'],

  css: [
    '~/assets/css/tailwind.css',
    '~/assets/css/print.css',
  ],

  vite: {
    plugins: [tailwindcss()],
  },

  nitro: {
    preset: 'static',
  },

  plugins: ['~/plugins/pinia.ts'],

  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE_URL,
    },
  },
})
