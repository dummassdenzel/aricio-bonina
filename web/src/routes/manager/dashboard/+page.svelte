<script lang="ts">
  import { onMount } from "svelte";
  import { api } from "$lib/services/api";
  import Chart from "chart.js/auto";
  import { formatDate } from "$lib/pipes/date-pipe";
  import UnitModal from "$lib/components/manager/unit-modal.svelte";
  import { dashboardStore } from "$lib/stores/dashboard-store";

  let error: string | null = null;
  let financialChart: Chart | null = null;

  // Subscribe to the store
  $: dashboardStats = $dashboardStore;

  let isUnitModalOpen = false;
  let selectedUnitNumber: number | null = null;

  const handleUnitClick = (unitNumber: string) => {
    selectedUnitNumber = parseInt(unitNumber);
    isUnitModalOpen = true;
  };

  // Update chart when dashboard stats change
  $: if (dashboardStats?.monthlyRevenue) {
    updateChart();
  }

  function updateChart() {
    if (financialChart) {
      financialChart.destroy();
    }

    const ctx = document.getElementById("financialChart") as HTMLCanvasElement;
    if (!ctx) return;

    financialChart = new Chart(ctx, {
      type: "line",
      data: {
        labels: dashboardStats.monthlyRevenue.labels,
        datasets: [
          {
            label: "Monthly Revenue",
            data: dashboardStats.monthlyRevenue.revenue,
            borderColor: "#14B8A6",
            tension: 0.4,
            fill: false,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function (value) {
                return "₱" + value.toLocaleString();
              },
            },
          },
        },
      },
    });
  }

  onMount(async () => {
    try {
      await dashboardStore.loadStats();
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
</script>

<h1 class="text-3xl font-bold text-teal">Dashboard</h1>

<div class="mt-6 flex flex-col gap-4">
  <div class="flex justify-between gap-4 rounded-2xl">
    <!-- left side -->
    <div class="grid grid-cols-1 gap-4 w-full">
      <!-- analytics -->
      <div
        class="w-full h-full bg-back border backdrop-blur-sm backdrop-filter rounded-xl p-4"
      >
        <canvas id="financialChart"></canvas>
      </div>
    </div>

    <!-- right side -->
    <div class="flex flex-col gap-2">
      <div
        class="w-80 h-20 border backdrop-blur-sm backdrop-filter rounded-xl p-4"
      >
        <p class="text-xs text-teal font-medium">All Tenants</p>
        <p class="text-3xl text-teal font-bold text-end font-inter">
          {dashboardStats.totalTenants}
        </p>
      </div>

      <div
        class="w-80 h-20 border backdrop-blur-sm backdrop-filter rounded-xl p-4"
      >
        <p class="text-xs text-teal font-medium">Available Units</p>
        <p class="text-3xl text-teal font-bold text-end font-inter">
          {vacantUnits}
        </p>
      </div>

      <div
        class="w-80 h-20 border backdrop-blur-sm backdrop-filter rounded-xl p-4"
      >
        <p class="text-xs text-teal font-medium font-inter">Occupied Units</p>
        <p class="text-3xl text-teal font-bold text-end font-inter">
          {dashboardStats.occupiedUnits} / {dashboardStats.totalUnits}
        </p>
      </div>

      <div
        class="w-80 h-20 border backdrop-blur-sm backdrop-filter rounded-xl p-4"
      >
        <p class="text-xs text-teal font-medium font-inter">
          Total Revenue for This Year
        </p>
        <p class="text-3xl text-brightgreen font-bold text-end font-inter">
          ₱{dashboardStats.yearlyRevenue.toLocaleString()}
        </p>
      </div>
    </div>
  </div>

  <h1 class="text-2xl font-bold text-teal mb-2 mt-2 font-inter">
    Lease Overview
  </h1>
  <div class="flex justify-between w-100 gap-4">
    <!-- recent renewals -->
    <div
      class="w-full h-auto border backdrop-blur-sm backdrop-filter rounded-xl font-inter p-4 flex flex-col items-center justify-center gap-2"
    >
      <p class="text-lg text-teal font-bold text-center font-inter">
        Total of Overdue
      </p>
      <p class="text-7xl text-slate font-bold text-center font-inter">
        {dashboardStats.overdueLease.length}
      </p>
    </div>

    <!-- overdue -->
    <div
      class="w-full h-auto bg-white border backdrop-blur-sm backdrop-filter rounded-xl font-inter p-4"
    >
      <p class="text-lg text-teal font-bold font-inter mb-4">
        Expired ({dashboardStats.overdueLease.length})
      </p>
      {#if dashboardStats.overdueLease.length > 0}
        <div class="max-h-24 overflow-y-auto flex flex-col gap-2">
          {#each dashboardStats.overdueLease as lease}
            <button
              class="justify-between bg-red20 p-3 rounded-lg cursor-pointer hover:bg-red10"
              on:click={() => handleUnitClick(lease.unit)}
            >
              <div class="flex justify-between items-center">
                <p class="font-inter text-teal font-medium text-sm">
                  Unit {lease.unit}
                </p>
                <span class="text-xs text-red"
                  >{lease.daysOverdue} days ago</span
                >
              </div>
              <!-- <p class="text-xs text-muted  "
                title={lease.tenants} >
                {lease.tenants}
              </p> -->
            </button>
          {/each}
        </div>
      {:else}
        <div class="bg-back p-4 rounded-lg mt-4">
          <p class="text-xs text-muted">No overdue leases.</p>
        </div>
      {/if}
    </div>

    <!-- expring soon -->
    <div
      class="w-full h-auto bg-white border backdrop-blur-sm backdrop-filter rounded-xl font-inter p-4"
    >
      <p class="text-lg text-teal font-bold font-inter mb-4">
        Expiring Soon ({dashboardStats.expiringSoon.length})
      </p>
      {#if dashboardStats.expiringSoon.length > 0}
        <div class="max-h-24 overflow-y-auto flex flex-col gap-2">
          {#each dashboardStats.expiringSoon as lease}
            <button
              class="justify-between bg-orange20 p-3 rounded-lg cursor-pointer hover:bg-orange10"
              on:click={() => handleUnitClick(lease.unit)}
            >
              <div class="flex justify-between items-center">
                <p class="font-inter text-teal font-medium text-sm">
                  Unit {lease.unit}
                </p>
                <span class="text-xs text-orange">{formatDate(lease.date)}</span
                >
              </div>
              <!-- <p class="text-xs text-muted  "
            title={lease.tenants} >
            {lease.tenants}
          </p> -->
            </button>
          {/each}
        </div>
      {:else}
        <div class="bg-back p-4 rounded-lg mt-4">
          <p class="text-xs text-muted">No lease expiring soon.</p>
        </div>
      {/if}
    </div>

    <!-- recent renewals -->
    <div
      class="w-full h-auto bg-white border backdrop-blur-sm backdrop-filter rounded-xl font-inter p-4"
    >
      <p class="text-lg text-teal font-bold font-inter mb-4">Recent Renewals</p>
      {#if dashboardStats.recentPayments.length > 0}
        <div class="max-h-24 overflow-y-auto flex flex-col gap-2">
          {#each dashboardStats.recentPayments as payment}
            <button
              class="justify-between bg-green20 p-3 rounded-lg cursor-pointer hover:bg-green10"
              on:click={() => handleUnitClick(payment.unit)}
            >
              <div class="flex justify-between items-center">
                <p class="font-inter text-teal font-medium text-sm">
                  Unit {payment.unit}
                </p>
                <span class="text-xs text-green font-medium"
                  >{formatDate(payment.date)}</span
                >
              </div>
            </button>
          {/each}
        </div>
      {:else}
        <div class="bg-back p-4 rounded-lg mt-4">
          <p class="text-xs text-muted">No recent renewals.</p>
        </div>
      {/if}
    </div>
  </div>
</div>

<!-- ======  O L D  V E R S I O N ======= -->

<!-- Stats Cards -->
<!-- <div class="bg-white p-6 rounded-lg shadow-sm">
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

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"> -->
<!-- Overdue Leases -->
<!-- <div class="bg-white p-6 rounded-lg shadow-sm">
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
  </div> -->

<!-- Leases Expiring Soon -->
<!-- <div class="bg-white p-6 rounded-lg shadow-sm">
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
  </div> -->

<!-- Recent Payments -->
<!-- <div class="bg-white p-6 rounded-lg shadow-sm">
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
  </div> -->
<!-- </div> -->

{#if selectedUnitNumber !== null}
  <UnitModal
    isOpen={isUnitModalOpen}
    unitNumber={selectedUnitNumber}
    onClose={() => {
      isUnitModalOpen = false;
      selectedUnitNumber = null;
    }}
  />
{/if}
