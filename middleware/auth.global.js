import { getToken, getExpiredAt } from "~/utils/token";

export default defineNuxtRouteMiddleware((to) => {
  if (!process.client) return;

  if (to.path === "/") return;

  const token = getToken();
  
  const expiredAt = getExpiredAt();

  if (!token || !expiredAt) {
    return navigateTo("/");
  }

  if (Date.now() > Number(expiredAt)) {
    localStorage.clear();
    sessionStorage.clear();
    return navigateTo("/");
  }
});