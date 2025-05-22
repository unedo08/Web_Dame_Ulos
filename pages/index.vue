<template>
    <div class="container-login">
        <div class="flex pt-2 h-screen">
            <!-- Left Side (Image and Description) -->
            <div class="w-8/12 bg-[#8E775E] text-white p-6 flex items-center justify-center">
                <p class="text-center">
                    "Dame Ulos is a local brand from Silindung (Tarutung) which focuses on preserving “Intangible
                    Cultural Heritage” namely “Ulos and Mandar Tarutung”. Each ulos and mandar is made with the
                    concept of “Revitalization” using natural dyes and preserving the traditional weaving tradition
                    (gedog) by following the original motif so that the philosophical values contained in the cloth
                    are maintained."
                </p>
            </div>

            <!-- Right Side (Login Form) -->
            <div class="w-4/12 bg-[#FFFFF0] p-6 flex-col justify-center items-center">
                <div class="image-container mb-50">
                    <img src="assets/image/DameUlosLogo 2.png" alt="Dame Ulos Logo" class="w-[290px] h-[184px]">
                </div>
                <h4 class="text-2xl font-semibold text-gray-900">Welcome Back!</h4>
                <h6 class="text-black-600 opacity-50 mb-6">Sign in to Continue to Dame Ulos Application</h6>
                <form @submit.prevent="handleLogin" class="w-full space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                        <input type="email" id="email" v-model="email"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md bg-[#FFF] focus:ring-2 focus:ring-gray-500" placeholder="Enter Your Email"
                            required />
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password:</label>
                        <input type="password" id="password" v-model="password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md bg-[#FFF] focus:ring-2 focus:ring-gray-500" placeholder="Enter Your Password"
                            required />
                    </div>
                    <div class="items-center">
                        <input type="checkbox" id="rememberMe" v-model="rememberMe"
                            class="h-4 w-4 border-gray-300 rounded focus:ring-2 focus:ring-gray-500" />
                        <label for="rememberMe" class="ml-2 text-sm text-gray-600" style="font-size: 18px;"><b>Remember Me</b></label>
                    </div>
                    <button type="submit"
                        class="w-full bg-[#BD9E77] text-white py-2 rounded-lg font-semibold hover:bg-[#a08c6a] focus:outline-none">
                        Sign in
                    </button>
                </form>
                <p v-if="errorMessage" class="text-red-500 mt-4 text-sm">{{ errorMessage }}</p>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

export default {
    name: 'LoginPage',
    setup() {
        const email = ref<string>('')
        const password = ref<string>('')
        const rememberMe = ref<boolean>(false)
        const errorMessage = ref<string | null>(null)
        const router = useRouter()
        const url = 'https://api-dame-ulos.databasedameulos.com'

        const handleLogin = async() => {
            // Simulate a login process
            errorMessage.value = null
            try{
                const response = await axios.post(`${url}/api/login`, {
                    email : email.value,
                    password : password.value
                })

                const token = response.data.token
                if(token){
                    if(rememberMe.value){
                        localStorage.setItem('auth_token', token)
                    }else{
                        sessionStorage.setItem('auth_token', token)
                    }

                    router.push('/beranda')
                }else{
                    errorMessage.value = 'Login Failed: token not found'
                }
            }catch(error: any){
                if(error.response && error.response.data && error.response.data.message){
                    errorMessage.value = error.response.data.message
                }else{
                    errorMessage.value = "Login Gagal. Silahkan coba lagi!"
                }
            }
        }

        return {
            email,
            password,
            rememberMe,
            errorMessage,
            handleLogin,
        }
    }
}

definePageMeta({
    layout: false
})
</script>

<style scoped>
* {
  font-family: 'Nunito', sans-serif;
}

.container-login {
    margin: 0;
    padding: 0;
    height: 100vh;
}

.flex {
    display: flex;
    height: 100%;
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
</style>
