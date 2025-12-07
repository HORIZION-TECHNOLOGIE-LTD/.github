// Global admin config
// Change API_BASE to your backend base URL when ready, e.g. "https://api.qrpay.agency"
const CONFIG = {
  API_BASE: "https://chibank.eu",
  // optional reverse-proxy on the frontend host, e.g. https://www.chibank.eu/api-proxy
  USE_PROXY: false,
  PROXY_BASE: "/api-proxy"
};

function apiUrl(path) {
  const base = CONFIG.API_BASE || "";
  // if base is empty, serve local mock under admin/api/
  if (!base) return `api/${path}`;
  // ensure no leading slash duplication
  // For admin API endpoints, prepend 'admin/' to the path
  return `${base.replace(/\/$/, "")}/admin/${path.replace(/^\//, "")}`;
}

// unified request with credentials and optional proxy routing
async function request(path, options = {}) {
  const url = apiUrl(path);
  const opts = {
    credentials: 'include',
    ...options,
    headers: {
      'Accept': 'application/json',
      ...(options.headers || {})
    }
  };
  // if proxy is enabled, rewrite to frontend host path
  if (CONFIG.USE_PROXY && typeof window !== 'undefined' && window.location) {
    const proxied = `${CONFIG.PROXY_BASE.replace(/\/$/, '')}/${path.replace(/^\//, '')}`;
    return fetch(proxied, opts);
  }
  return fetch(url, opts);
}
