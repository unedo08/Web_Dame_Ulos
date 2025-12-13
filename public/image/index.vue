<template>
  <div class="flex min-h-screen">
    <!-- LEFT -->
    <div class="w-full md:w-1/3 bg-[#FFFFF0] flex flex-col justify-center items-center px-10">
      <img
        src="/assets/image/DameUlosLogo2.png"
        alt="Dame Ulos Logo"
        class="w-[220px] mb-8"
      />

      <h2 class="text-2xl font-bold mb-1">Welcome Back!</h2>
      <p class="text-sm text-gray-500 mb-6 text-center">
        Sign in to Continue to Dame Ulos Application
      </p>

      <form @submit.prevent="handleLogin" class="w-full max-w-sm space-y-4">
        <div>
          <label class="text-sm font-medium">Email</label>
          <input
            v-model="email"
            type="email"
            placeholder="Enter Your Email"
            class="w-full border rounded-md px-4 py-2 mt-1"
          />
        </div>

        <div>
          <label class="text-sm font-medium">Password</label>
          <input
            v-model="password"
            type="password"
            placeholder="Enter Your Password"
            class="w-full border rounded-md px-4 py-2 mt-1"
          />
        </div>

        <div class="flex items-center gap-2">
          <input type="checkbox" v-model="rememberMe" />
          <span class="text-sm font-semibold">Remember Me</span>
        </div>

        <button
          type="submit"
          class="w-full bg-[#BD9E77] text-white py-2 rounded-lg font-semibold hover:bg-[#a08c6a]"
        >
          Sign in
        </button>
      </form>
    </div>

    <!-- RIGHT (COLLAGE) -->
    <div class="hidden md:block w-2/3 relative">
      <!-- GRID IMAGE -->
      <div
        class="absolute inset-0 bg-cover bg-center"
        style="background-image: url('/assets/image/Background-Login.png')"
      ></div>

      <!-- OVERLAY -->
      <div class="absolute inset-0 bg-[#8E775E]/85 flex items-center justify-center px-12">
        <p class="text-white text-center max-w-3xl leading-relaxed">
          "Dame Ulos is a local brand from Silindung (Tarutung) which focuses on
          preserving <b>Intangible Cultural Heritage</b> namely <b>Ulos and Mandar
          Tarutung</b>. Each ulos and mandar is made with the concept of
          <b>Revitalization</b> using natural dyes and preserving the traditional
          weaving tradition (gedog) by following the original motif so that the
          philosophical values contained in the cloth are maintained."
        </p>
      </div>
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
const router = useRouter();
const url = ref("");

onMounted(() => {
  url.value = useRuntimeConfig().public.apiBase;
});

const handleLogin = async () => {
  const res = await axios.post(`${url.value}/api/login`, {
    email: email.value,
    password: password.value,
  });

  sessionStorage.setItem("name", res.data.user.name);
  sessionStorage.setItem("role", res.data.user.role.id);
  sessionStorage.setItem("auth_token", res.data.token);

  router.push("/beranda");
};

definePageMeta({ layout: false });
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
