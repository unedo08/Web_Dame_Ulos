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

    setToken(
      res.data.token,
      res.data.expires_in
    );

    console.info("[AUTH] Token pre-refresh berhasil");
  } catch (err) {
    console.warn(
      "[AUTH] Pre-refresh gagal, menunggu interceptor"
    );
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
    // logout();
  }
};

onMounted(() => {
  checkSession();

  timer = setInterval(
    checkSession,
    CHECK_INTERVAL
  );
});

onUnmounted(() => {
  clearInterval(timer);
});

definePageMeta({
  middleware: "auth",
});
</script>


<template>
  <div class="app-layout">
    <Sidebar />
    <div class="main-area">
      <Topbar />
      <main class="page-content">
        <slot />
      </main>
    </div>
  </div>
</template>


<style scoped>
* {
  font-family: "Nunito", sans-serif;
}

.app-layout {
  min-height: 100vh;
  background: #fff;
}

.main-area {
  min-height: 100vh;
  margin-left: 16rem;
}

.page-content {
  min-height: 100vh;
  padding-top: 60px;
  padding-left: 24px;
  padding-right: 24px;
}

/* =========================================
   MOBILE
========================================= */

@media (max-width: 767px) {

  .main-area {
    margin-left: 0;
    width: 100%;
  }

  .page-content {
    width: 100%;
    padding-top: 60px;
    padding-left: 16px;
    padding-right: 16px;
  }

}
</style>