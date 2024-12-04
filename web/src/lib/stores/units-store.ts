import { writable } from 'svelte/store';
import { api } from '$lib/services/api';

function createUnitsStore() {
    const { subscribe, set, update } = writable<any[]>([]);

    return {
        subscribe,
        loadUnits: async () => {
            try {
                const response = await api.get("units");
                console.log("Loaded units from store!");
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