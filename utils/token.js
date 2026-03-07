export const clearToken = () => {
  localStorage.removeItem("auth_token");
  localStorage.removeItem("expired_at");

  sessionStorage.removeItem("auth_token");
  sessionStorage.removeItem("expired_at");
};

export const isTokenStillValid = () => {
  const expiredAt = getExpiredAt();
  if (!expiredAt) return false;

  return Date.now() < expiredAt;
};

export const getToken = () => {
  return (
    localStorage.getItem("auth_token") ||
    sessionStorage.getItem("auth_token")
  );
};

export const getExpiredAt = () => {
  return Number(
    localStorage.getItem("expired_at") ||
    sessionStorage.getItem("expired_at")
  );
};

export const setToken = (token, expiresIn) => {
  const expiredAt = Date.now() + expiresIn * 1000;

  localStorage.setItem("auth_token", token);
  localStorage.setItem("expired_at", expiredAt);

  sessionStorage.setItem("auth_token", token);
  sessionStorage.setItem("expired_at", expiredAt);
};