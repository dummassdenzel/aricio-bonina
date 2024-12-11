import { writable } from 'svelte/store';
import { api } from '$lib/services/api';

function createUnitsStore() {
    const { subscribe, set, update } = writable<any[]>([]);

    return {
        subscribe,
        loadUnits: async (params?: { search?: string, status?: string, floor?: string }) => {
            try {
                let endpoint = "units";
                if (params) {
                    const queryParams = new URLSearchParams();
                    if (params.search) queryParams.append('search', params.search);
                    if (params.status) queryParams.append('status', params.status);
                    if (params.floor) queryParams.append('floor', params.floor);
                    endpoint += `?${queryParams.toString()}`;
                }
                const response = await api.get(endpoint);
                set(response.payload);
            } catch (err: any) {
                console.error('Error loading units:', err);
                throw err;
            }
        },
        reset: () => set([])
    };
}

export const unitsStore = createUnitsStore();