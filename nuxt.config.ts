import tailwindcss from '@tailwindcss/vite'

export default defineNuxtConfig({

  // ----------------------------
  // 🔥 WAJIB UNTUK DEPLOY SUBFOLDER
  // ----------------------------
  app: {
    baseURL: '/dame-ulos/',              // path folder tempat kamu deploy
    buildAssetsDir: '_nuxt/',            // default Nuxt asset folder
    head: {
      link: [
        { rel: "icon", type: "image/x-icon", href: "/dame-ulos/favicon.ico" }
      ]
    }
  },

  // ----------------------------
  // MODULES
  // ----------------------------
  modules: ['shadcn-nuxt'],

  shadcn: {
    prefix: '',
    componentDir: './components/ui',
  },

  // ----------------------------
  // GLOBAL CSS
  // ----------------------------
  css: [
    '~/assets/css/tailwind.css',
    '~/assets/css/print.css',
  ],

  // ----------------------------
  // VITE CONFIG
  // ----------------------------
  vite: {
    plugins: [
      tailwindcss(),
    ],
  },

  // ----------------------------
  // ROUTER AUTH MIDDLEWARE
  // ----------------------------
  router: {
    middleware: ['auth']
  },

  // ----------------------------
  // PINIA
  // ----------------------------
  plugins: ['~/plugins/pinia.ts'],

  // ----------------------------
  // API CONFIG
  // ----------------------------
  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE_URL
    }
  },

  // ----------------------------
  // NUXT BUILD COMPATIBILITY
  // ----------------------------
  compatibilityDate: '2025-04-02',
})
