import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', () => {
  const isAuthenticated = useState<boolean>('isAuthenticated', () => false)

  const login = () => {
    isAuthenticated.value = true
  }

  const logout = () => {
    isAuthenticated.value = false
  }

  return {
    isAuthenticated,
    login,
    logout
  }
})
