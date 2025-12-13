<template>
  <div class="h-screen bg-gray-100 flex justify-center font-nunito">
    <div class="w-full max-w-[1200px] h-full bg-white rounded-xl shadow-xl overflow-hidden flex">
      <div class="w-full md:w-1/3 bg-[#FFFFF0] flex flex-col justify-center items-center px-10">
        <img src="/assets/image/DameUlosLogo2.png" alt="Dame Ulos Logo" class="w-[220px] mb-8" />

        <h2 class="text-2xl font-bold mb-1 text-black">
          Welcome Back!
        </h2>

        <p class="text-sm text-gray-500 mb-8 text-center">
          Sign in to Continue to Dame Ulos Application
        </p>

        <form @submit.prevent="handleLogin" class="w-full max-w-sm space-y-5">
          <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input v-model="email" type="email" placeholder="Enter Your Email" required
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-[#BD9E77]" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Password</label>
            <input v-model="password" type="password" placeholder="Enter Your Password" required
              class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-[#BD9E77]" />
          </div>

          <div class="flex items-center gap-2">
            <input type="checkbox" v-model="rememberMe" />
            <span class="text-sm font-semibold">Remember Me</span>
          </div>

          <button type="submit"
            class="w-full bg-[#BD9E77] text-white py-2 rounded-lg font-semibold hover:bg-[#a08c6a] transition">
            Sign in
          </button>
        </form>
      </div>
      <div class="hidden md:block w-2/3 relative bg-cover bg-center"
        style="background-image: url('/image/Background-Login.png')">
        <div class="absolute inset-0 bg-[#8E775E]/70 flex items-center justify-center px-16">
          <p class="text-white text-center max-w-3xl text-lg leading-relaxed">
            “Dame Ulos is a local brand from Silindung (Tarutung) which focuses on
            preserving <b>Intangible Cultural Heritage</b> namely
            <b>Ulos and Mandar Tarutung</b>. Each ulos and mandar is made with the
            concept of <b>Revitalization</b> using natural dyes and preserving the
            traditional weaving tradition (gedog) by following the original motif
            so that the philosophical values contained in the cloth are maintained.”
          </p>
        </div>
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
  try {
    const res = await axios.post(`${url.value}/api/login`, {
      email: email.value,
      password: password.value,
    });

    sessionStorage.setItem("name", res.data.user.name);
    sessionStorage.setItem("role", res.data.user.role.id);
    sessionStorage.setItem("auth_token", res.data.token);

    router.push("/beranda");
  } catch (err) {
    alert("Login gagal");
  }
};

definePageMeta({
  layout: false,
});
</script>

<style scoped>
.font-nunito {
  font-family: "Nunito", sans-serif;
}
</style>
