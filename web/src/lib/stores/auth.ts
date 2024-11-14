import { writable } from "svelte/store";

interface User {
  id: number;
  email: string;
  first_name: string;
  last_name: string;
}

interface AuthState {
  isAuthenticated: boolean;
  token: string | null;
  user: User | null;
}

function createAuthStore() {
  const { subscribe, set, update } = writable<AuthState>({
    isAuthenticated: false,
    token: null,
    user: null,
  });

  return {
    subscribe,
    login: (token: string, user: User) => {
      localStorage.setItem("token", token);
      localStorage.setItem("user", JSON.stringify(user));
      set({ isAuthenticated: true, token, user });
    },
    logout: () => {
      localStorage.removeItem("token");
      localStorage.removeItem("user");
      set({ isAuthenticated: false, token: null, user: null });
    },
    initialize: () => {
      const token = localStorage.getItem("token");
      const user = JSON.parse(localStorage.getItem("user") || "null");
      if (token && user) {
        set({ isAuthenticated: true, token, user });
      }
    },
  };
}

export const auth = createAuthStore();
