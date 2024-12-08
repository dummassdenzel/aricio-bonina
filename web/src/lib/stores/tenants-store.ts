import { writable } from 'svelte/store';
import { api } from '$lib/services/api';

function createTenantsStore() {
    const { subscribe, set, update } = writable<any[]>([]);

    return {
        subscribe,
        loadTenants: async () => {
            try {
                const response = await api.get("tenants");
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
