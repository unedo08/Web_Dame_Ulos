import { createPinia } from 'pinia'

export default defineNuxtPlugin((nuxtApp) => {
  // Create Pinia instance
  const pinia = createPinia()
  
  // Add it to the Nuxt app instance
  nuxtApp.vueApp.use(pinia)
})
