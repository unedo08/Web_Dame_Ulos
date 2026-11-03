<template>
    <div class="container-login">
        <div class="login-layout">
            <!-- Left Side (Image and Description) -->
            <div class="login-brand bg-[#8E775E] text-white p-6 flex items-center justify-center">
                <p class="text-center">
                    "Dame Ulos is a local brand from Silindung (Tarutung) which focuses on preserving “Intangible
                    Cultural Heritage” namely “Ulos and Mandar Tarutung”. Each ulos and mandar is made with the
                    concept of “Revitalization” using natural dyes and preserving the traditional weaving tradition
                    (gedog) by following the original motif so that the philosophical values contained in the cloth
                    are maintained."
                </p>
            </div>

            <!-- Right Side (Login Form) -->
            <div class="login-panel bg-[#FFFFF0] p-6">
                <div class="image-container mb-50">
                    <img src="assets/image/DameUlosLogo2.png" alt="Dame Ulos Logo" class="login-logo">
                </div>
                <h4 class="text-2xl font-semibold text-gray-900">Welcome Back!</h4>
                <h6 class="text-black-600 opacity-50 mb-6">Sign in to Continue to Dame Ulos Application</h6>
                <form @submit.prevent="handleLogin" class="w-full space-y-4" novalidate>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                        <input type="email" id="email" v-model="email"
                            :aria-invalid="Boolean(fieldErrors.email)"
                            :class="{ 'login-input-error': fieldErrors.email }"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md bg-[#FFF] focus:ring-2 focus:ring-gray-500"
                            placeholder="Enter Your Email" @input="clearLoginErrors" />
                        <p v-if="fieldErrors.email" class="login-field-error" role="alert">
                            {{ fieldErrors.email }}
                        </p>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password:</label>
                        <input type="password" id="password" v-model="password"
                            :aria-invalid="Boolean(fieldErrors.password)"
                            :class="{ 'login-input-error': fieldErrors.password }"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md bg-[#FFF] focus:ring-2 focus:ring-gray-500"
                            placeholder="Enter Your Password" @input="clearLoginErrors" />
                        <p v-if="fieldErrors.password" class="login-field-error" role="alert">
                            {{ fieldErrors.password }}
                        </p>
                    </div>
                    <div class="items-center">
                        <input type="checkbox" id="rememberMe" v-model="rememberMe"
                            class="h-4 w-4 border-gray-300 rounded focus:ring-2 focus:ring-gray-500" />
                        <label for="rememberMe" class="ml-2 text-sm text-gray-600"><b>Remember Me</b></label>
                    </div>
                    <button type="submit"
                        class="w-full bg-[#BD9E77] text-white py-2 rounded-lg font-semibold hover:bg-[#a08c6a] focus:outline-none">
                        Sign in
                    </button>
                    <p v-if="errorMessage" class="login-error-alert" role="alert" aria-live="polite">
                        {{ errorMessage }}
                    </p>
                </form>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: 'LoginPage',
  setup() {
    const email = ref<string>('')
    const password = ref<string>('')
    const errorMessage = ref<string | null>(null)
    const fieldErrors = ref<{ email: string; password: string }>({ email: '', password: '' })
    const rememberMe = ref<boolean>(false)
    const router = useRouter()
    const { $axios } = useNuxtApp()

    const clearLoginErrors = () => {
      fieldErrors.value = { email: '', password: '' }
      errorMessage.value = null
    }

    const handleLogin = async () => {
      fieldErrors.value = {
        email: email.value.trim() ? '' : 'email wajib diisi.',
        password: password.value ? '' : 'password wajib diisi.'
      }
      errorMessage.value = null

      if (fieldErrors.value.email || fieldErrors.value.password) {
        return
      }

      try {
        const response = await $axios.post('/login', {
          email: email.value.trim(),
          password: password.value,
          rememberMe: rememberMe.value
        })

        if (response.data.success) {
          router.push('/beranda')
        } else {
          errorMessage.value = 'Email atau password yang dimasukkan salah atau belum terdaftar.'
        }
      } catch (error: unknown) {
        let responseStatus: number | undefined
        if (typeof error === 'object' && error !== null && 'response' in error) {
          const response = error.response
          if (typeof response === 'object' && response !== null && 'status' in response &&
              typeof response.status === 'number') {
            responseStatus = response.status
          }
        }

        if (responseStatus === 400 || responseStatus === 401 || responseStatus === 403 ||
            responseStatus === 404 || responseStatus === 422) {
          errorMessage.value = 'Email atau password yang dimasukkan salah atau belum terdaftar.'
        } else {
          errorMessage.value = 'Tidak dapat memproses login saat ini. Silakan coba lagi.'
        }
      }
    }

    return {
      email,
      password,
      errorMessage,
      fieldErrors,
      clearLoginErrors,
      handleLogin,
      rememberMe
    }
  }
}

definePageMeta({
  layout: false
})
</script>

<style scoped>
.container-login {
    margin: 0;
    padding: 0;
    min-height: 100vh;
    min-height: 100dvh;
}

.login-layout {
    display: flex;
    min-height: 100vh;
    min-height: 100dvh;
}

.login-brand {
    width: 66.666667%;
}

.login-panel {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 33.333333%;
}

.login-logo {
    width: 290px;
    height: 184px;
    object-fit: contain;
}

.text-center {
    text-align: center;
}

.image-container {
    display: flex;
    justify-content: center;
    align-items: center;
    place-items: center;
    height: 15vh;
    margin-top: 30px;
}

.login-field-error,
.login-error-alert {
    color: #dc2626;
    font-size: 14px;
    line-height: 1.4;
}

.login-field-error {
    margin-top: 4px;
}

.login-input-error {
    border-color: #dc2626 !important;
}

.login-error-alert {
    margin: 0;
    padding: 10px 12px;
    border: 1px solid #fecaca;
    border-radius: 6px;
    background: #fef2f2;
}

@media (max-width: 767px) {
    .container-login {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px 16px;
        box-sizing: border-box;
        background: #fffff0;
    }

    .login-layout {
        width: 100%;
        min-height: 0;
        align-items: center;
        justify-content: center;
        padding-top: 0;
    }

    .login-brand {
        display: none;
    }

    .login-panel {
        width: 100%;
        max-width: 420px;
        padding: 28px 22px;
        border-radius: 16px;
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.1);
        box-sizing: border-box;
    }

    .image-container {
        height: auto;
        margin: 0 auto 20px;
    }

    .login-logo {
        width: min(220px, 70vw);
        height: auto;
    }

    .login-panel input:not([type="checkbox"]) {
        font-size: 14px;
    }
}
</style>
