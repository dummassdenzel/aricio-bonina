const BASE_URL = "http://localhost/aricio-bonina/api";

async function fetchWithAuth(endpoint: string, options: RequestInit = {}) {
  const token = localStorage.getItem("token");

  const headers = {
    "Content-Type": "application/json",
    ...(token && { Authorization: `Bearer ${token}` }),
    ...options.headers,
  };

  const response = await fetch(`${BASE_URL}/${endpoint}`, {
    ...options,
    headers,
  });

  if (!response.ok) {
    if (endpoint === "login") {
      const errorData = await response.json();
      throw new Error(errorData.status.message);
    }

    if (response.status === 401) {
      localStorage.removeItem("token");
      localStorage.removeItem("user");
      window.location.href = "/web/login";
      throw new Error("Session expired. Please login again.");
    }
    throw new Error("Network response was not ok");
  }

  return response.json();
}

export const api = {
  get: (endpoint: string) => fetchWithAuth(endpoint),
  post: (endpoint: string, data: any) =>
    fetchWithAuth(endpoint, {
      method: "POST",
      body: JSON.stringify(data),
    }),
  // Add other methods as needed
};
