import axios from "axios";
import { authState } from "./authState";

const api = axios.create();

let failedQueue = [];

const processQueue = (error, token = null) => {
  failedQueue.forEach(prom => {
    error ? prom.reject(error) : prom.resolve(token);
  });
  failedQueue = [];
};

api.interceptors.request.use((config) => {
  if (typeof window === "undefined") return config;

  const token = sessionStorage.getItem("auth_token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;

    if (
      error.response?.status === 401 &&
      !originalRequest._retry
    ) {
      if (authState.isRefreshing) {
        return new Promise((resolve, reject) => {
          failedQueue.push({
            resolve: (token) => {
              originalRequest.headers.Authorization = `Bearer ${token}`;
              resolve(api(originalRequest));
            },
            reject,
          });
        });
      }

      originalRequest._retry = true;
      authState.isRefreshing = true;

      try {
        const token = sessionStorage.getItem("auth_token");

        const res = await axios.post(
          "/api/refresh",
          {},
          { headers: { Authorization: `Bearer ${token}` } }
        );

        const newToken = res.data.token;
        const newExpiredAt = Date.now() + res.data.expires_in * 1000;

        sessionStorage.setItem("auth_token", newToken);
        sessionStorage.setItem("expired_at", newExpiredAt);

        api.defaults.headers.common.Authorization = `Bearer ${newToken}`;
        processQueue(null, newToken);

        return api(originalRequest);
      } catch (err) {
        processQueue(err, null);
        sessionStorage.clear();
        window.location.href = "/";
        return Promise.reject(err);
      } finally {
        authState.isRefreshing = false;
      }
    }

    return Promise.reject(error);
  }
);

export default api;