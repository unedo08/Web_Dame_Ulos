import axios from "axios";
import { authState } from "./authState";
import { getToken, setToken, clearToken } from "./token";

const api = axios.create();

let failedQueue = [];

const processQueue = (error, token = null) => {
  failedQueue.forEach(p =>
    error ? p.reject(error) : p.resolve(token)
  );
  failedQueue = [];
};

api.interceptors.request.use((config) => {
  if (typeof window === "undefined") return config;

  const token = getToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

api.interceptors.response.use(
  res => res,
  async error => {
    const originalRequest = error.config;

    if (
      error.response?.status === 401 &&
      !originalRequest._retry
    ) {
      if (authState.isRefreshing) {
        return new Promise((resolve, reject) => {
          failedQueue.push({
            resolve: token => {
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
        const token = getToken();

        const res = await axios.post(
          "/api/refresh",
          {},
          { headers: { Authorization: `Bearer ${token}` } }
        );

        setToken(res.data.token, res.data.expires_in);
        api.defaults.headers.common.Authorization =
          `Bearer ${res.data.token}`;

        processQueue(null, res.data.token);
        return api(originalRequest);
      } catch (err) {
        processQueue(err, null);
        clearToken();
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