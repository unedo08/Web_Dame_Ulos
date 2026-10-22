<template>
  <div class="login-page min-h-screen bg-gray-100 flex items-center justify-center font-nunito">
    <div class="login-card bg-white rounded-xl shadow-xl flex overflow-hidden">
      <title>Login</title>
      <div class="login-form-panel bg-[#FFFFF0] px-10 py-12">
        <img src="/assets/image/DameUlosLogo2.png" class="w-[180px] mb-8" />

        <h2 class="text-2xl font-bold mb-1">Welcome Back!</h2>
        <p class="text-sm text-gray-500 mb-6">
          Sign in to Continue to Dame Ulos Application
        </p>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <div>
            <label class="text-sm font-medium block mb-1">Email</label>
            <input v-model="email" type="email" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Masukkan Email" />
          </div>

          <div>
            <label class="text-sm font-medium block mb-1">Password</label>
            <input v-model="password" type="password" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Masukkan Password" />
          </div>

          <div class="flex items-center gap-2 text-sm mt-2">
            <input type="checkbox" v-model="rememberMe" />
            Remember me
          </div>

          <button class="mt-4 w-full bg-[#BD9E77] text-white py-2 rounded-md
                   hover:bg-[#a08c6a] transition">
            Sign In
          </button>
        </form>
      </div>

      <div class="login-image-panel relative">
        <img src="/image/Background-Login.png" class="w-full h-full object-cover border border-black" />

        <div class="absolute inset-0 flex items-center justify-center px-14">
          <p class="text-white text-center text-sm leading-relaxed max-w-md italic">
            “Dame Ulos is a local brand from Silindung (Tarutung) which focuses on
            preserving Intangible Cultural Heritage namely Ulos and Mandar Tarutung.
            Each ulos and mandar is made with the concept of Revitalization using
            natural dyes and preserving the traditional weaving tradition (gedog)
            by following the original motif so that the philosophical values
            contained in the cloth are maintained.”
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
import Swal from "sweetalert2";

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
    sessionStorage.clear();
    const res = await axios.post(`${url.value}/api/login`, {
      email: email.value,
      password: password.value,
    });
    const expiresAt = Date.now() + res.data.expires_in * 1000;

    sessionStorage.setItem("name", res.data.user.name);
    sessionStorage.setItem("role", res.data.user.role.id);
    sessionStorage.setItem("auth_token", res.data.token);
    sessionStorage.setItem("expired_at", expiresAt);

    localStorage.setItem("name", res.data.user.name);
    localStorage.setItem("role", res.data.user.role.id);
    localStorage.setItem("auth_token", res.data.token);
    localStorage.setItem("expired_at", expiresAt);

    router.push("/beranda");
  } catch (err) {
    Swal.fire({
      title: "Gagal!",
      text: "Terjadi kesalahan saat menambahkan order.",
      icon: "error",
    });
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

.login-card {
  width: min(1100px, calc(100% - 48px));
  min-height: 727px;
}

.login-form-panel {
  flex: 0 0 420px;
  box-sizing: border-box;
}

.login-image-panel {
  width: 680px;
  height: 727px;
  flex: 1 1 auto;
}

@media (max-width: 767px) {
  .login-page {
    min-height: 100vh;
    min-height: 100dvh;
    padding: 20px 16px;
    box-sizing: border-box;
  }

  .login-card {
    width: 100%;
    max-width: 420px;
    min-height: 0;
    height: auto;
    border-radius: 16px;
  }

  .login-form-panel {
    flex: 1 1 auto;
    width: 100%;
    padding: 28px 22px;
  }

  .login-image-panel {
    display: none;
  }

  .login-form-panel input:not([type="checkbox"]) {
    font-size: 16px;
  }
}
</style>
