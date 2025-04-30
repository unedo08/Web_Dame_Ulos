import axios from 'axios'

export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()

  const instance = axios.create({
    baseURL: config.public.apiBase,
    headers: {
      'Content-Type': 'application/json'
    }
  })

  // Tambahkan token ke semua request jika ada
  instance.interceptors.request.use((config) => {
    const token = localStorage.getItem('token') || sessionStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  })

  return {
    provide: {
      axios: instance
    }
  }
})
