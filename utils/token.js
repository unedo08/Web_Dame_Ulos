export const getToken = () =>
  sessionStorage.getItem("auth_token");

export const getExpiredAt = () =>
  Number(sessionStorage.getItem("expired_at"));

export const setToken = (token, expiresIn) => {
  sessionStorage.setItem("auth_token", token);
  sessionStorage.setItem(
    "expired_at",
    Date.now() + expiresIn * 1000
  );
};

export const clearToken = () => {
  sessionStorage.removeItem("auth_token");
  sessionStorage.removeItem("expired_at");
};

export const isTokenStillValid = () => {
  const expiredAt = getExpiredAt();
  if (!expiredAt) return false;
  return Date.now() < expiredAt;
};
