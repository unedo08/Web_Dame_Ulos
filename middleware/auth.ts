export default defineNuxtRouteMiddleware(() => {
  // ⛔ JANGAN AKSES sessionStorage DI SERVER
  if (process.server) return

  const token = sessionStorage.getItem('auth_token')

  if (!token) {
    return navigateTo('/')
  }
})
