<script setup>
import { onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";

const { $api } = useNuxtApp();
const router = useRouter();

const CHECK_INTERVAL = 5000;
const REFRESH_BEFORE = 60 * 1000;

let sessionChecker = null;
let isRefreshing = false;

const logout = async () => {
  try {
    const token = sessionStorage.getItem("auth_token");
    if (token) {
      await $api.post("/api/logout");
    }
  } catch (err) {
    console.warn("Logout API error (ignored)", err);
  } finally {
    sessionStorage.clear();
    router.replace("/");
  }
};

const refreshToken = async () => {
  if (isRefreshing) return;
  isRefreshing = true;

  try {
    const res = await $api.post("/api/refresh");

    const newToken = res.data.token;
    const newExpiredAt = Date.now() + res.data.expires_in * 1000;

    sessionStorage.setItem("auth_token", newToken);
    sessionStorage.setItem("expired_at", newExpiredAt);

    console.info("Token berhasil di-refresh");
  } catch (err) {
    console.error("Refresh token gagal", err);
    logout();
  } finally {
    isRefreshing = false;
  }
};

const checkSession = () => {
  const expiredAt = Number(sessionStorage.getItem("expired_at"));

  if (!expiredAt) {
    logout();
    return;
  }

  const now = Date.now();
  const diff = expiredAt - now;

  if (diff <= REFRESH_BEFORE && diff > 0) {
    refreshToken();
  }

  if (diff <= 0) {
    logout();
  }
};

onMounted(() => {
  sessionChecker = setInterval(checkSession, CHECK_INTERVAL);
});

onUnmounted(() => {
  clearInterval(sessionChecker);
});
</script>

<template>
  <div class="flex flex-col h-screen bg-gray-100">
    <div class="flex flex-grow">
      <div class="w-64 bg-[#520000] text-white p-6 shrink-0">
        <Sidebar />
      </div>

      <div class="flex-1 bg-white">
        <Topbar />
        <div class="mt-2 px-6">
          <slot />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Topbar from "../components/Topbar/index.vue";

export default {
  name: "DefaultLayout",
  components: { Topbar },
};

definePageMeta({
  middleware: "auth",
});
</script>

<style scoped>
* {
  font-family: "Nunito", sans-serif;
}
</style>
