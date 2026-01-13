const roleMap = {
  "1": "super-admin",
  "2": "admin",
  "3": "marketing",
  "4": "quality-control",
  "5": "packaging",
  "6": "pewarna-alam",
  "7": "sosial-media",
};

const routeRoleMap = {
  "/beranda": ["super-admin", "admin", "marketing", "quality-control", "packaging", "sosial-media"],

  "/customer": ["super-admin", "admin", "marketing", "packaging"],

  "/staff": ["super-admin", "admin"],

  "/code": ["super-admin", "admin", "quality-control"],

  "/entry": ["super-admin", "admin", "marketing", "quality-control"],

  "/live": ["super-admin", "admin", "marketing", "packaging"],

  "/kasir": ["super-admin", "admin", "marketing", "packaging", "sosial-media"],

  "/packaging-page": ["super-admin", "packaging"],

  "/inventory": ["super-admin", "admin", "quality-control", "packaging"],

  "/acara": ["super-admin", "admin", "marketing", "sosial-media"],

  "/pewarnaAlam": ["super-admin", "pewarna-alam"],

  "/statistik": ["super-admin", "admin"],

  "/databasePenjualan": ["super-admin", "admin"],

  "/databaseInventory": ["super-admin", "admin"],
};

export default defineNuxtRouteMiddleware((to) => {
  const roleId = sessionStorage.getItem("role");
  const role = roleMap[roleId];

  if (!role) {
    return navigateTo("/");
  }

  const allowedRoles =
    to.meta.roles || routeRoleMap[to.path];
  // if (!allowedRoles) {
  //   return role === "super-admin"
  //     ? true
  //     : navigateTo("/403");
  // }

  // if (!allowedRoles.includes(role)) {
  //   return navigateTo("/403");
  // }
});
