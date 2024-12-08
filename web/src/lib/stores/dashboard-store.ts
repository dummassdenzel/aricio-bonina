import { writable } from 'svelte/store';
import { api } from '$lib/services/api';

interface DashboardStats {
    totalTenants: number;
    occupiedUnits: number;
    totalUnits: number;
    overdueLease: Array<{
        unit: string;
        tenants: string;
        daysOverdue: number;
    }>;
    expiringSoon: Array<{
        unit: string;
        tenants: string;
        date: string;
    }>;
    recentPayments: Array<{
        unit: string;
        amount: number;
        date: string;
    }>;
    monthlyRevenue: {
        labels: string[];
        revenue: number[];
    };
    yearlyRevenue: number;
}

function createDashboardStore() {
    const { subscribe, set, update } = writable<DashboardStats>({
        totalTenants: 0,
        occupiedUnits: 0,
        totalUnits: 0,
        overdueLease: [],
        expiringSoon: [],
        recentPayments: [],
        monthlyRevenue: {
            labels: [],
            revenue: [],
        },
        yearlyRevenue: 0
    });

    return {
        subscribe,
        loadStats: async () => {
            try {
                const response = await api.get("dashboard-stats");
                set(response.payload);
            } catch (err: any) {
                console.error('Error loading dashboard stats:', err);
                throw err;
            }
        },
        reset: () => set({
            totalTenants: 0,
            occupiedUnits: 0,
            totalUnits: 0,
            overdueLease: [],
            expiringSoon: [],
            recentPayments: [],
            monthlyRevenue: {
                labels: [],
                revenue: [],
            },
            yearlyRevenue: 0
        })
    };
}

export const dashboardStore = createDashboardStore();