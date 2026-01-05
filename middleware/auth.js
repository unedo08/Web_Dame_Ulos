export default defineNuxtRouteMiddleware((to) => {
  if (to.path === "/") return;

  const token = sessionStorage.getItem("auth_token");
  const expiredAt = sessionStorage.getItem("expired_at");

  if (!token || !expiredAt) {
    return navigateTo("/");
  }

  if (Date.now() > Number(expiredAt)) {
    sessionStorage.clear();
    return navigateTo("/");
  }
});
