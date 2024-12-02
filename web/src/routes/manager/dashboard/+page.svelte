<script lang="ts">
  import { onMount } from "svelte";
  import { api } from "$lib/services/api";

  let error: string | null = null;
  let success: string | null = null;

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
  }

  let dashboardStats: DashboardStats = {
    totalTenants: 0,
    occupiedUnits: 0,
    totalUnits: 0,
    overdueLease: [],
    expiringSoon: [],
    recentPayments: [],
  };

  onMount(async () => {
    try {
      const response = await api.get("dashboard-stats");
      console.log(response);
      dashboardStats = response.payload;
    } catch (err: any) {
      error = err.message;
    }
  });

  $: occupancyRate = dashboardStats.totalUnits
    ? Math.round(
        (dashboardStats.occupiedUnits / dashboardStats.totalUnits) * 100,
      )
    : 0;

  $: vacantUnits = dashboardStats.totalUnits - dashboardStats.occupiedUnits;

  // Helper function to format dates
  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString("en-US", {
      year: "numeric",
      month: "short",
      day: "numeric",
    });
  };
</script>

<h1 class="text-3xl font-bold text-teal mb-6">Dashboard</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
  <!-- Stats Cards -->
  <div class="bg-white p-6 rounded-lg shadow-sm">
    <h3 class="text-gray-500 text-sm font-medium">Total Tenants</h3>
    <p class="text-2xl font-bold text-teal mt-2">
      {dashboardStats.totalTenants}
    </p>
  </div>

  <div class="bg-white p-6 rounded-lg shadow-sm">
    <h3 class="text-gray-500 text-sm font-medium">Occupancy</h3>
    <p class="text-2xl font-bold text-teal mt-2">
      {dashboardStats.occupiedUnits}/{dashboardStats.totalUnits}
      <span class="text-sm text-gray-500 ml-2">({occupancyRate}%)</span>
    </p>
  </div>

  <div class="bg-white p-6 rounded-lg shadow-sm">
    <h3 class="text-gray-500 text-sm font-medium">Vacant Units</h3>
    <p class="text-2xl font-bold text-teal mt-2">
      {vacantUnits}
    </p>
  </div>

  <div class="bg-white p-6 rounded-lg shadow-sm">
    <h3 class="text-gray-500 text-sm font-medium">Overdue Leases</h3>
    <p class="text-2xl font-bold text-red-500 mt-2">
      {dashboardStats.overdueLease.length}
    </p>
  </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  <!-- Overdue Leases -->
  <div class="bg-white p-6 rounded-lg shadow-sm">
    <h2 class="text-xl font-semibold mb-4">Overdue Leases</h2>
    {#if dashboardStats.overdueLease.length > 0}
      <div class="space-y-3">
        {#each dashboardStats.overdueLease as lease}
          <div class="flex justify-between items-center border-b pb-2">
            <div>
              <p class="font-medium">Unit {lease.unit}</p>
              <p
                class="text-sm text-gray-500 max-w-[200px] truncate"
                title={lease.tenants}
              >
                {lease.tenants}
              </p>
            </div>
            <span class="text-red-500">{lease.daysOverdue} days</span>
          </div>
        {/each}
      </div>
    {:else}
      <p class="text-gray-500">No overdue leases</p>
    {/if}
  </div>

  <!-- Leases Expiring Soon -->
  <div class="bg-white p-6 rounded-lg shadow-sm">
    <h2 class="text-xl font-semibold mb-4">Leases Expiring Soon</h2>
    {#if dashboardStats.expiringSoon.length > 0}
      <div class="space-y-3">
        {#each dashboardStats.expiringSoon as lease}
          <div class="flex justify-between items-center border-b pb-2">
            <div>
              <p class="font-medium">Unit {lease.unit}</p>
              <p
                class="text-sm text-gray-500 max-w-[200px] truncate"
                title={lease.tenants}
              >
                {lease.tenants}
              </p>
            </div>
            <span class="text-gray-500">{formatDate(lease.date)}</span>
          </div>
        {/each}
      </div>
    {:else}
      <p class="text-gray-500">No leases expiring soon</p>
    {/if}
  </div>

  <!-- Recent Payments -->
  <div class="bg-white p-6 rounded-lg shadow-sm">
    <h2 class="text-xl font-semibold mb-4">Recent Lease Renewals</h2>
    {#if dashboardStats.recentPayments.length > 0}
      <div class="space-y-3">
        {#each dashboardStats.recentPayments as payment}
          <div class="flex justify-between items-center border-b pb-2">
            <div>
              <p class="font-medium">Unit {payment.unit}</p>
            </div>
            <div>
              <p class="text-green-500 font-medium">₱{payment.amount}</p>
              <p class="text-sm text-gray-500">{formatDate(payment.date)}</p>
            </div>
          </div>
        {/each}
      </div>
    {:else}
      <p class="text-gray-500">No recent renewals.</p>
    {/if}
  </div>
</div>
