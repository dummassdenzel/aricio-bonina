<script lang="ts">
  import { onMount } from "svelte";
  import { api } from "$lib/services/api";
  import Chart from "chart.js/auto";
  import { formatDate } from "$lib/pipes/date-pipe";

  let error: string | null = null;

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

  // HARD  CODED DATA FOR CHART
  const monthlyData = {
    labels: ["January", "February", "March", "April", "May", "June"],
    revenue: [45000, 48000, 47000, 49000, 46000, 50000],
  };

  onMount(async () => {
    try {
      const response = await api.get("dashboard-stats");
      console.log(response);
      dashboardStats = response.payload;

      // INITIALIZE CHART
      const ctx = document.getElementById(
        "financialChart",
      ) as HTMLCanvasElement;
      new Chart(ctx, {
        // TYPE OF CHART
        type: "line",
        data: {
          // X-AXIS LABELS(yung months)
          labels: monthlyData.labels,
          // DATASET TO BE PLOTTED
          datasets: [
            {
              // LABEL OF THE DATASET
              label: "Monthly Revenue",
              // DATA POINTS
              data: monthlyData.revenue,
              // COLOR OF THE LINE
              borderColor: "#14B8A6",
              // SMOOTHNESS OF THE LINE
              tension: 0.4,
              // IF THE LINE SHOULD BE FILLED
              fill: false,
            },
          ],
        },
        // CHART CONFIGURATION OPTIONS
        options: {
          // MAKES THE CHART RESIZE IF ITS CONTAINER (yung div) RESIZES
          responsive: true,
          // MAINTAINS THE ASPECT RATIO OF THE CHART
          maintainAspectRatio: false,
          plugins: {
            legend: {
              // POSITION OF THE LEGEND BOX
              position: "top",
            },
            title: {
              // IF THE TITLE SHOULD BE DISPLAYED
              display: true,
              // TITLE OF THE CHART
              text: "Monthly Revenue",
            },
          },
          scales: {
            y: {
              // IF THE CHART SHOULD START FROM ZERO
              beginAtZero: true,
              ticks: {
                // FORMAT Y-AXIS VALUES WITH PESO SIGN
                callback: (value) => "₱" + value.toLocaleString(),
              },
            },
          },
        },
      });
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
          Maintenance Units
        </p>
        <p class="text-3xl text-teal font-bold text-end font-inter">
          {dashboardStats.overdueLease.length}
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
      <p class="text-8xl text-slate font-bold text-center font-inter">
        {dashboardStats.overdueLease.length}
      </p>
    </div>

    <!-- overdue -->
    <div
      class="w-full h-auto bg-white border backdrop-blur-sm backdrop-filter rounded-xl font-inter p-4"
    >
      <p class="text-lg text-teal font-bold font-inter mb-4">Overdue</p>
      {#if dashboardStats.overdueLease.length > 0}
        <div class="max-h-24 overflow-y-auto flex flex-col gap-2">
          {#each dashboardStats.overdueLease as lease}
            <div class="justify-between bg-back p-3 rounded-lg">
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
            </div>
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
      <p class="text-lg text-teal font-bold font-inter mb-4">Expiring Soon</p>
      {#if dashboardStats.expiringSoon.length > 0}
        <div class="max-h-24 overflow-y-auto flex flex-col gap-2">
          {#each dashboardStats.expiringSoon as lease}
            <div class="justify-between bg-back p-3 rounded-lg">
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
            </div>
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
            <div class="justify-between bg-back p-3 rounded-lg">
              <div class="flex justify-between items-center">
                <p class="font-inter text-teal font-medium text-sm">
                  Unit {payment.unit}
                </p>
                <span class="text-xs text-orange"
                  >{formatDate(payment.date)}</span
                >
              </div>
            </div>
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
