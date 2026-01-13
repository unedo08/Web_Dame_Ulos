import axios from "axios";

const api = axios.create();

api.interceptors.request.use(async (config) => {
  if (typeof window === "undefined") return config;

  const token = sessionStorage.getItem("auth_token");
  const expiredAt = sessionStorage.getItem("expired_at");

  if (!token || !expiredAt) return config;

  if (Date.now() < Number(expiredAt)) {
    config.headers.Authorization = `Bearer ${token}`;
    return config;
  }

  try {
    const res = await axios.post(
      `${api.defaults.baseURL}/api/refresh`,
      {},
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    );

    const newToken = res.data.token;
    const newExpiredAt = Date.now() + res.data.expires_in * 1000;

    sessionStorage.setItem("auth_token", newToken);
    sessionStorage.setItem("expired_at", newExpiredAt);

    config.headers.Authorization = `Bearer ${newToken}`;
    return config;
  } catch (err) {
    sessionStorage.clear();
    window.location.href = "/";
    return Promise.reject(err);
  }
});

export default api;
