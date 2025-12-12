import tailwindcss from '@tailwindcss/vite'

export default defineNuxtConfig({
  // ...
  modules: ['shadcn-nuxt'],
  shadcn: {
    prefix: '',
    componentDir: './components/ui'
  },
  css: ['~/assets/css/tailwind.css', '~/assets/css/print.css'],
  vite: {
    plugins: [
      tailwindcss(),
    ],
  },
  router: {
    middleware: ['auth']
  },
  plugins: ['~/plugins/pinia.ts'],
  runtimeConfig: {
    public:{
      apiBase: process.env.API_BASE_URL
    }
  },
  compatibilityDate: '2025-04-02',
})