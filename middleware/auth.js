// export default defineNuxtRouteMiddleware((to) => {
//   if (to.path === "/") return;

//   const token = sessionStorage.getItem("auth_token");
//   const expiredAt = sessionStorage.getItem("expired_at");

//   if (!token || !expiredAt) {
//     return navigateTo("/");
//   }

//   if (Date.now() > Number(expiredAt)) {
//     sessionStorage.clear();
//     return navigateTo("/");
//   }
// });

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