<script setup>
import { onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { authState } from "~/utils/authState";
import {
  getExpiredAt,
  isTokenStillValid,
  setToken,
  clearToken
} from "~/utils/token";
import refreshApi from "~/utils/refreshApi";

const { $api } = useNuxtApp();
const router = useRouter();

const CHECK_INTERVAL = 5000;
const REFRESH_BEFORE = 60 * 1000;
const GRACE_PERIOD = 3000;

let timer = null;

const logout = async () => {
  try {
    await $api.post("/api/logout");
  } catch {

  } finally {
    clearToken();
    router.replace("/");
  }
};

const preRefresh = async () => {
  if (authState.isRefreshing) return;
  if (!isTokenStillValid()) return;

  authState.isRefreshing = true;

  try {
    const res = await refreshApi.post("/api/refresh");
    setToken(res.data.token, res.data.expires_in);
    console.info("[AUTH] Token pre-refresh berhasil");
  } catch (err) {
    console.warn("[AUTH] Pre-refresh gagal, menunggu interceptor");
  } finally {
    authState.isRefreshing = false;
  }
};

const checkSession = () => {
  if (document.visibilityState !== "visible") return;

  if (authState.isRefreshing) return;

  const expiredAt = getExpiredAt();
  if (!expiredAt) return;

  const diff = expiredAt - Date.now();

  if (diff <= REFRESH_BEFORE && diff > 0) {
    preRefresh();
    return;
  }

  if (diff <= -GRACE_PERIOD) {
    logout();
  }
};

onMounted(() => {
  checkSession();
  timer = setInterval(checkSession, CHECK_INTERVAL);
});

onUnmounted(() => {
  clearInterval(timer);
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