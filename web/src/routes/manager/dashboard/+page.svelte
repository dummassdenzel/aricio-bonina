<script lang="ts">
  import { onMount } from "svelte";
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
        maintainAspectRatio: false,
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

<div class="">
  <h1 class="text-2xl sm:text-3xl font-bold text-teal">Dashboard</h1>

  <div class="mt-6 flex flex-col gap-6">
    <!-- Top Section -->
    <div class="flex flex-col lg:flex-row gap-6">
      <!-- Chart -->
      <div class="w-full lg:w-2/3">
        <div class="bg-back border rounded-xl p-4 sm:p-6">
          <!-- Chart Title -->
          <h2 class="text-lg sm:text-xl font-bold text-teal mb-4">
            Monthly Revenue
          </h2>
          <div class="h-[300px] sm:h-[400px]">
            <canvas id="financialChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="w-full lg:w-1/3 grid grid-cols-2 lg:grid-cols-1 gap-4">
        <div
          class="border rounded-xl p-4 sm:p-6 bg-white hover:shadow-sm transition-shadow"
        >
          <p class="text-xs sm:text-sm text-teal font-medium">All Tenants</p>
          <p
            class="text-xl sm:text-2xl text-teal font-bold text-end font-inter mt-2"
          >
            {dashboardStats.totalTenants}
          </p>
        </div>

        <div
          class="border rounded-xl p-4 sm:p-6 bg-white hover:shadow-sm transition-shadow"
        >
          <p class="text-xs sm:text-sm text-teal font-medium">
            Available Units
          </p>
          <p
            class="text-xl sm:text-2xl text-teal font-bold text-end font-inter mt-2"
          >
            {vacantUnits}
          </p>
        </div>

        <div
          class="border rounded-xl p-4 sm:p-6 bg-white hover:shadow-sm transition-shadow"
        >
          <p class="text-xs sm:text-sm text-teal font-medium">Occupancy Rate</p>
          <p
            class="text-xl sm:text-2xl text-teal font-bold text-end font-inter mt-2"
          >
            {dashboardStats.occupiedUnits} / {dashboardStats.totalUnits}
            <span class="text-sm text-slate ml-2">({occupancyRate}%)</span>
          </p>
        </div>

        <div
          class="border rounded-xl p-4 sm:p-6 bg-white hover:shadow-sm transition-shadow"
        >
          <p class="text-xs sm:text-sm text-teal font-medium">
            Total Revenue (This Year)
          </p>
          <p
            class="text-xl sm:text-2xl text-brightgreen font-bold text-end font-inter mt-2"
          >
            ₱{dashboardStats.yearlyRevenue.toLocaleString()}
          </p>
        </div>
      </div>
    </div>

    <!-- Lease Overview Section -->
    <div class="mt-4">
      <h2 class="text-xl sm:text-2xl font-bold text-teal mb-6">
        Lease Overview
      </h2>

      <div
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6"
      >
        <!-- Total Overdue -->
        <div
          class="border rounded-xl p-6 bg-white hover:shadow-sm transition-shadow"
        >
          <p class="text-base sm:text-lg text-teal font-bold text-center mb-4">
            Total of Expired Leases
          </p>
          <p
            class="text-4xl sm:text-5xl lg:text-6xl text-slate font-bold text-center"
          >
            {dashboardStats.overdueLease.length}
          </p>
        </div>

        <!-- Lists Section -->
        <div
          class="lg:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6"
        >
          <!-- Overdue List -->
          <div
            class="border rounded-xl p-4 sm:p-6 bg-white hover:shadow-sm transition-shadow"
          >
            <p class="text-base sm:text-lg text-teal font-bold mb-4">
              Expired ({dashboardStats.overdueLease.length})
            </p>
            {#if dashboardStats.overdueLease.length > 0}
              <div
                class="max-h-[200px] overflow-y-auto flex flex-col gap-2 pr-2"
              >
                {#each dashboardStats.overdueLease as lease}
                  <button
                    class="w-full bg-red20 p-3 rounded-lg hover:bg-red10 transition-colors"
                    on:click={() => handleUnitClick(lease.unit)}
                  >
                    <div class="flex justify-between items-center">
                      <p class="text-teal font-medium text-sm">
                        Unit {lease.unit}
                      </p>
                      <span class="text-xs text-red"
                        >{lease.daysOverdue} days ago</span
                      >
                    </div>
                  </button>
                {/each}
              </div>
            {:else}
              <p class="text-xs text-muted bg-back p-3 rounded-lg">
                No overdue leases.
              </p>
            {/if}
          </div>

          <!-- Expiring Soon -->
          <div
            class="border rounded-xl p-4 sm:p-6 bg-white hover:shadow-sm transition-shadow"
          >
            <p class="text-base sm:text-lg text-teal font-bold mb-4">
              Expiring Soon ({dashboardStats.expiringSoon.length})
            </p>
            {#if dashboardStats.expiringSoon.length > 0}
              <div
                class="max-h-[200px] overflow-y-auto flex flex-col gap-2 pr-2"
              >
                {#each dashboardStats.expiringSoon as lease}
                  <button
                    class="w-full bg-orange20 p-3 rounded-lg hover:bg-orange10 transition-colors"
                    on:click={() => handleUnitClick(lease.unit)}
                  >
                    <div class="flex justify-between items-center">
                      <p class="text-teal font-medium text-sm">
                        Unit {lease.unit}
                      </p>
                      <span class="text-xs text-orange"
                        >{formatDate(lease.date)}</span
                      >
                    </div>
                  </button>
                {/each}
              </div>
            {:else}
              <p class="text-xs text-muted bg-back p-3 rounded-lg">
                No lease expiring soon.
              </p>
            {/if}
          </div>

          <!-- Recent Renewals -->
          <div
            class="border rounded-xl p-4 sm:p-6 bg-white hover:shadow-sm transition-shadow"
          >
            <p class="text-base sm:text-lg text-teal font-bold mb-4">
              Recent Renewals
            </p>
            {#if dashboardStats.recentPayments.length > 0}
              <div
                class="max-h-[200px] overflow-y-auto flex flex-col gap-2 pr-2"
              >
                {#each dashboardStats.recentPayments as payment}
                  <button
                    class="w-full bg-green20 p-3 rounded-lg hover:bg-green10 transition-colors"
                    on:click={() => handleUnitClick(payment.unit)}
                  >
                    <div class="flex justify-between items-center">
                      <p class="text-teal font-medium text-sm">
                        Unit {payment.unit}
                      </p>
                      <span class="text-xs text-green"
                        >{formatDate(payment.date)}</span
                      >
                    </div>
                  </button>
                {/each}
              </div>
            {:else}
              <p class="text-xs text-muted bg-back p-3 rounded-lg">
                No recent renewals.
              </p>
            {/if}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

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
