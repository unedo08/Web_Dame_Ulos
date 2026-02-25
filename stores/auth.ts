import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', () => {
    // Menyimpan status autentikasi
    const isAuthenticated = useState<boolean>('isAuthenticated', () => false)

    // Fungsi untuk login
    const login = () => {
        isAuthenticated.value = true
    }

    // Fungsi untuk logout
    const logout = () => {
        isAuthenticated.value = false
    }

    return {
        isAuthenticated,
        login,
        // logout
    }
})
