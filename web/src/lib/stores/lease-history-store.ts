import { writable } from 'svelte/store';
import { api } from '$lib/services/api';

interface PaginationData {
    currentPage: number;
    totalPages: number;
    totalItems: number;
    itemsPerPage: number;
}

function createLeaseHistoryStore() {
    const { subscribe, set, update } = writable<{
        leases: Record<string, any[]>;
        pagination: PaginationData;
    }>({
        leases: {},
        pagination: {
            currentPage: 1,
            totalPages: 1,
            totalItems: 0,
            itemsPerPage: 10
        }
    });

    return {
        subscribe,
        loadLeaseHistory: async (params?: { 
            search?: string, 
            status?: string,
            page?: number,
            limit?: number 
        }) => {
            try {
                let endpoint = "lease-history";
                if (params) {
                    const queryParams = new URLSearchParams();
                    if (params.search) queryParams.append('search', params.search);
                    if (params.status) queryParams.append('status', params.status);
                    if (params.page) queryParams.append('page', params.page.toString());
                    if (params.limit) queryParams.append('limit', params.limit.toString());
                    endpoint += `?${queryParams.toString()}`;
                }
                const response = await api.get(endpoint);
                set(response.payload);
            } catch (err: any) {
                console.error('Error loading lease history:', err);
                throw err;
            }
        },
        reset: () => set({
            leases: {},
            pagination: {
                currentPage: 1,
                totalPages: 1,
                totalItems: 0,
                itemsPerPage: 10
            }
        })
    };
}

export const leaseHistoryStore = createLeaseHistoryStore();