<template>
  <div class="flex flex-col md:flex-row min-h-screen">
    <!-- Left Side: 4/12 -->
    <div
      class="flex-[4] bg-[#FFFFF0] flex flex-col justify-center items-center p-6"
    >
      <div class="image-container mb-10">
        <img
          src="assets/image/DameUlosLogo2.png"
          alt="Dame Ulos Logo"
          class="w-[220px] md:w-[290px] h-auto"
        />
      </div>
      <h4 class="text-2xl font-semibold text-gray-900 mb-1">Welcome Back!</h4>
      <h6 class="text-black-600 opacity-50 mb-6 text-center text-sm">
        Sign in to Continue to Dame Ulos Application
      </h6>
      <form @submit.prevent="handleLogin" class="w-full max-w-sm space-y-4">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700"
            >Email:</label
          >
          <input
            type="email"
            id="email"
            v-model="email"
            required
            placeholder="Enter Your Email"
            class="w-full px-4 py-2 border border-gray-300 rounded-md bg-white focus:ring-2 focus:ring-gray-500"
          />
        </div>
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700"
            >Password:</label
          >
          <input
            type="password"
            id="password"
            v-model="password"
            required
            placeholder="Enter Your Password"
            class="w-full px-4 py-2 border border-gray-300 rounded-md bg-white focus:ring-2 focus:ring-gray-500"
          />
        </div>
        <div class="flex items-center">
          <input
            type="checkbox"
            id="rememberMe"
            v-model="rememberMe"
            class="h-4 w-4 border-gray-300 rounded focus:ring-2 focus:ring-gray-500"
          />
          <label for="rememberMe" class="ml-2 text-sm text-gray-600 font-bold"
            >Remember Me</label
          >
        </div>
        <button
          type="submit"
          class="w-full bg-[#BD9E77] text-white py-2 rounded-lg font-semibold hover:bg-[#a08c6a] focus:outline-none"
        >
          Sign in
        </button>
      </form>
      <p v-if="errorMessage" class="text-red-500 mt-4 text-sm">
        {{ errorMessage }}
      </p>
    </div>

    <!-- Right Side: 8/12 -->
    <div
      class="flex-[8] bg-[#8E775E] text-white p-6 flex items-center justify-center"
    >
      <p class="text-center max-w-3xl px-4">
        "Dame Ulos is a local brand from Silindung (Tarutung) which focuses on
        preserving “Intangible Cultural Heritage” namely “Ulos and Mandar
        Tarutung”. Each ulos and mandar is made with the concept of
        “Revitalization” using natural dyes and preserving the traditional
        weaving tradition (gedog) by following the original motif so that the
        philosophical values contained in the cloth are maintained."
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import { useRuntimeConfig } from "#imports";

const email = ref("");
const password = ref("");
const rememberMe = ref(false);
const errorMessage = ref(null);
const router = useRouter();
const url = ref("");

onMounted(() => {
  const config = useRuntimeConfig();
  url.value = config.public.apiBase;
});

const handleLogin = async () => {
  errorMessage.value = null;
  try {
    const response = await axios.post(`${url.value}/api/login`, {
      email: email.value,
      password: password.value,
    });
    
    const token = response.data.token;
    if (token) {
      if (rememberMe.value) {
        localStorage.setItem("auth_token", token);
        localStorage.setItem("email", email.value);
        localStorage.setItem("password", password.value);
      }

      sessionStorage.setItem("auth_token", token);
      sessionStorage.setItem("email", response.data.user.email);
      sessionStorage.setItem("role", response.data.user.name);

      router.push("/beranda");
    } else {
      errorMessage.value = "Login Failed: token not found";
    }
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || "Login Gagal. Silahkan coba lagi!";
  }
};

definePageMeta({
  layout: false,
});
</script>

<style scoped>
* {
  font-family: "Nunito", sans-serif;
}

.image-container {
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>
