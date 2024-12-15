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

  let activeChart: "revenue" | "tenants" | "units" = "revenue";

  // Watch for chart type changes
  $: if (activeChart) {
    updateChart();
  }

  function updateChart() {
    if (financialChart) {
      financialChart.destroy();
    }

    const ctx = document.getElementById("financialChart") as HTMLCanvasElement;
    if (!ctx) return;

    const chartConfig = {
      revenue: {
        label: "Monthly Revenue",
        data: dashboardStats.monthlyRevenue.revenue,
        borderColor: "#14B8A6",
        yAxisLabel: "Revenue (₱)",
        formatter: (value: number) => "₱" + value.toLocaleString(),
      },
      tenants: {
        label: "Total Tenants",
        data: dashboardStats.monthlyTenants.counts,
        borderColor: "#6366F1",
        yAxisLabel: "Number of Tenants",
        formatter: (value: number) => value.toString(),
      },
      units: {
        label: "Occupied Units",
        data: dashboardStats.monthlyOccupiedUnits.counts,
        borderColor: "#F59E0B",
        yAxisLabel: "Number of Units",
        formatter: (value: number) => value.toString(),
      },
    };

    const currentConfig = chartConfig[activeChart];

    financialChart = new Chart(ctx, {
      type: "line",
      data: {
        labels: dashboardStats.monthlyRevenue.labels,
        datasets: [
          {
            label: currentConfig.label,
            data: currentConfig.data,
            borderColor: currentConfig.borderColor,
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
          tooltip: {
            callbacks: {
              label: function (context) {
                return currentConfig.formatter(context.parsed.y);
              },
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: currentConfig.yAxisLabel,
            },
            ticks: {
              callback: function (value) {
                return currentConfig.formatter(value as number);
              },
            },
          },
        },
        animation: {
          duration: 750,
          easing: "easeInOutQuart",
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

  const currentYear = new Date().getFullYear();
</script>

<div class="">
  <h1 class="text-2xl sm:text-3xl font-bold text-teal">Dashboard</h1>

  <div class="mt-6 flex flex-col gap-6">
    <!-- Top Section -->
    <div class="flex flex-col lg:flex-row gap-6">
      <!-- Chart -->
      <div class="w-full lg:w-2/3">
        <div class="bg-back border rounded-xl p-4 sm:p-6">
          <!-- Chart Controls -->
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg sm:text-xl font-bold text-teal">
              {activeChart === "revenue"
                ? "Monthly Revenue"
                : activeChart === "tenants"
                  ? "Monthly Tenants"
                  : "Occupied Units"}
            </h2>
            <div class="flex gap-2">
              <button
                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors"
                class:bg-teal={activeChart === "revenue"}
                class:text-white={activeChart === "revenue"}
                class:bg-back={activeChart !== "revenue"}
                class:text-slate={activeChart !== "revenue"}
                on:click={() => (activeChart = "revenue")}
              >
                Revenue
              </button>
              <button
                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors"
                class:bg-teal={activeChart === "tenants"}
                class:text-white={activeChart === "tenants"}
                class:bg-back={activeChart !== "tenants"}
                class:text-slate={activeChart !== "tenants"}
                on:click={() => (activeChart = "tenants")}
              >
                Tenants
              </button>
              <button
                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors"
                class:bg-teal={activeChart === "units"}
                class:text-white={activeChart === "units"}
                class:bg-back={activeChart !== "units"}
                class:text-slate={activeChart !== "units"}
                on:click={() => (activeChart = "units")}
              >
                Units
              </button>
            </div>
          </div>

          <!-- Chart Container -->
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
            Total Revenue ({currentYear})
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
      <h2 class="text-xl sm:text-2xl font-bold text-teal mb-4 sm:mb-6">
        Lease Overview
      </h2>

      <div class="flex flex-col lg:flex-row gap-4 sm:gap-6">
        <!-- Total Overdue Card -->
        <div class="w-full lg:w-1/4">
          <div
            class="border rounded-xl p-4 sm:p-6 bg-white hover:shadow-sm transition-shadow h-full"
          >
            <p
              class="text-sm sm:text-base lg:text-lg text-teal font-bold text-center mb-2 sm:mb-4"
            >
              Total of Expired Leases
            </p>
            <p
              class="text-3xl sm:text-4xl lg:text-5xl text-slate font-bold text-center"
            >
              {dashboardStats.overdueLease.length}
            </p>
          </div>
        </div>

        <!-- Lists Section -->
        <div class="w-full lg:w-3/4">
          <div
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 h-full"
          >
            <!-- Overdue List -->
            <div
              class="border rounded-xl p-4 sm:p-6 bg-white hover:shadow-sm transition-shadow"
            >
              <p
                class="text-sm sm:text-base lg:text-lg text-teal font-bold mb-4"
              >
                Expired ({dashboardStats.overdueLease.length})
              </p>
              {#if dashboardStats.overdueLease.length > 0}
                <div
                  class="max-h-[150px] sm:max-h-[200px] overflow-y-auto flex flex-col gap-2 pr-2"
                >
                  {#each dashboardStats.overdueLease as lease}
                    <button
                      class="w-full bg-red20 p-2 sm:p-3 rounded-lg hover:bg-red10 transition-colors"
                      on:click={() => handleUnitClick(lease.unit)}
                    >
                      <div class="flex justify-between items-center">
                        <p class="text-xs sm:text-sm text-teal font-medium">
                          Unit {lease.unit}
                        </p>
                        <span class="text-[10px] sm:text-xs text-red"
                          >{lease.daysOverdue} days ago</span
                        >
                      </div>
                    </button>
                  {/each}
                </div>
              {:else}
                <p class="text-xs sm:text-sm text-muted bg-back p-3 rounded-lg">
                  No overdue leases.
                </p>
              {/if}
            </div>

            <!-- Expiring Soon -->
            <div
              class="border rounded-xl p-4 sm:p-6 bg-white hover:shadow-sm transition-shadow"
            >
              <p
                class="text-sm sm:text-base lg:text-lg text-teal font-bold mb-4"
              >
                Expiring Soon ({dashboardStats.expiringSoon.length})
              </p>
              {#if dashboardStats.expiringSoon.length > 0}
                <div
                  class="max-h-[150px] sm:max-h-[200px] overflow-y-auto flex flex-col gap-2 pr-2"
                >
                  {#each dashboardStats.expiringSoon as lease}
                    <button
                      class="w-full bg-orange20 p-2 sm:p-3 rounded-lg hover:bg-orange10 transition-colors"
                      on:click={() => handleUnitClick(lease.unit)}
                    >
                      <div class="flex justify-between items-center">
                        <p class="text-xs sm:text-sm text-teal font-medium">
                          Unit {lease.unit}
                        </p>
                        <span class="text-[10px] sm:text-xs text-orange"
                          >{formatDate(lease.date)}</span
                        >
                      </div>
                    </button>
                  {/each}
                </div>
              {:else}
                <p class="text-xs sm:text-sm text-muted bg-back p-3 rounded-lg">
                  No lease expiring soon.
                </p>
              {/if}
            </div>

            <!-- Recent Renewals -->
            <div
              class="border rounded-xl p-4 sm:p-6 bg-white hover:shadow-sm transition-shadow"
            >
              <p
                class="text-sm sm:text-base lg:text-lg text-teal font-bold mb-4"
              >
                Recent Renewals
              </p>
              {#if dashboardStats.recentPayments.length > 0}
                <div
                  class="max-h-[150px] sm:max-h-[200px] overflow-y-auto flex flex-col gap-2 pr-2"
                >
                  {#each dashboardStats.recentPayments as payment}
                    <button
                      class="w-full bg-green20 p-2 sm:p-3 rounded-lg hover:bg-green10 transition-colors"
                      on:click={() => handleUnitClick(payment.unit)}
                    >
                      <div class="flex justify-between items-center">
                        <p class="text-xs sm:text-sm text-teal font-medium">
                          Unit {payment.unit}
                        </p>
                        <span class="text-[10px] sm:text-xs text-green"
                          >{formatDate(payment.date)}</span
                        >
                      </div>
                    </button>
                  {/each}
                </div>
              {:else}
                <p class="text-xs sm:text-sm text-muted bg-back p-3 rounded-lg">
                  No recent renewals.
                </p>
              {/if}
            </div>
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
