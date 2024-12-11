import { writable } from 'svelte/store';
import { api } from '$lib/services/api';

function createTenantsStore() {
    const { subscribe, set, update } = writable<any[]>([]);

    return {
        subscribe,
        loadTenants: async (params?: { search?: string, status?: string }) => {
            try {
                let endpoint = "tenants";
                if (params) {
                    const queryParams = new URLSearchParams();
                    if (params.search) queryParams.append('search', params.search);
                    if (params.status) queryParams.append('status', params.status);
                    endpoint += `?${queryParams.toString()}`;
                }
                const response = await api.get(endpoint);
                set(response.payload);
            } catch (err: any) {
                console.error('Error loading tenants:', err);
                throw err;
            }
        },
        reset: () => set([])
    };
}

export const tenantsStore = createTenantsStore();
