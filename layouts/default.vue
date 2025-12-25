<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import { useRuntimeConfig } from "#imports";

const router = useRouter();
const IDLE_LIMIT = 15 * 60 * 1000;
const WARNING_TIME = 60 * 1000;
const CHECK_INTERVAL = 5000;

let idleTimer = null;
let sessionChecker = null;
const showWarning = ref(false);
const remainingSeconds = ref(60);
const url = ref("");

onMounted(() => {
  url.value = useRuntimeConfig().public.apiBase;
});

const logout = async () => {
  try {
    const token = sessionStorage.getItem("auth_token");
    if (token && url) {
      await axios.post(
        `${url.value}/api/logout`,
        {},
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );
    }
  } catch (err) {
    console.warn("Logout API error (ignored)", err);
  } finally {
    sessionStorage.clear();
    router.replace("/");
  }
};

const resetIdleTimer = () => {
  clearTimeout(idleTimer);
  idleTimer = setTimeout(() => {
    console.warn("Logout karena idle");
    logout();
  }, IDLE_LIMIT);
};

const activityEvents = [
  "mousemove",
  "mousedown",
  "keydown",
  "scroll",
  "touchstart",
];

const checkSessionExpired = () => {
  const expiredAt = Number(sessionStorage.getItem("expired_at"));
  if (!expiredAt) return logout();

  const now = Date.now();
  const diff = expiredAt - now;
  if (diff <= WARNING_TIME && diff > 0) {
    showWarning.value = true;
    remainingSeconds.value = Math.ceil(diff / 1000);
  }

  if (diff <= 0) {
    logout();
  }
};

const logoutNow = () => logout();
const extendSession = () => {
  showWarning.value = false;
  resetIdleTimer();
};

onMounted(() => {
  activityEvents.forEach(e =>
    window.addEventListener(e, resetIdleTimer)
  );

  resetIdleTimer();
  sessionChecker = setInterval(checkSessionExpired, CHECK_INTERVAL);
});

onUnmounted(() => {
  activityEvents.forEach(e =>
    window.removeEventListener(e, resetIdleTimer)
  );

  clearTimeout(idleTimer);
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
          <div class="w-full max-w-full">
            <slot />
          </div>
        </div>
      </div>
    </div>

    <div v-if="showWarning" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-[360px]">
        <h3 class="text-lg font-semibold mb-2 text-red-600">
          Session akan habis
        </h3>

        <p class="text-sm text-gray-700 mb-4">
          Anda akan logout otomatis dalam
          <span class="font-bold text-red-600">
            {{ remainingSeconds }} detik
          </span>.
        </p>

        <div class="flex justify-end gap-3">
          <button class="px-4 py-2 text-sm rounded bg-gray-200 hover:bg-gray-300" @click="logoutNow">
            Logout sekarang
          </button>

          <button class="px-4 py-2 text-sm rounded bg-red-600 text-white hover:bg-red-700" @click="extendSession">
            Tetap Login
          </button>
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