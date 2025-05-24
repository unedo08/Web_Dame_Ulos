import tailwindcss from '@tailwindcss/vite'

export default defineNuxtConfig({
  // ...
  modules: ['shadcn-nuxt'],
  shadcn: {
    /**
     * Prefix for all the imported component
     */
    prefix: '',
    /**
     * Directory that the component lives in.
     * @default "./components/ui"
     */
    componentDir: './components/ui'
  },

  // css: ['bootstrap/dist/css/bootstrap.css', 'assets/css/tailwind.css'],
  css: ['~/assets/css/tailwind.css'],
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