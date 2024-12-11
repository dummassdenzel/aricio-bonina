import { writable } from 'svelte/store';
import { api } from '$lib/services/api';

function createLeaseHistoryStore() {
    const { subscribe, set, update } = writable<Record<string, any[]>>({});

    return {
        subscribe,
        loadLeaseHistory: async (params?: { search?: string, status?: string }) => {
            try {
                let endpoint = "lease-history";
                if (params) {
                    const queryParams = new URLSearchParams();
                    if (params.search) queryParams.append('search', params.search);
                    if (params.status) queryParams.append('status', params.status);
                    endpoint += `?${queryParams.toString()}`;
                }
                const response = await api.get(endpoint);
                set(response.payload);
            } catch (err: any) {
                console.error('Error loading lease history:', err);
                throw err;
            }
        },
        reset: () => set({})
    };
}

export const leaseHistoryStore = createLeaseHistoryStore();