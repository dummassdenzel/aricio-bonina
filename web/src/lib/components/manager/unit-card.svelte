<script lang="ts">
  import { formatDate } from "$lib/pipes/date-pipe";

  export let unitNumber: string;
  export let floor: string;
  export let current_lease: {
    tenants: Array<{
      first_name: string;
      last_name: string;
    }>;
    end_date: string;
  } | null;

  $: isOccupied = current_lease !== null;

  $: tenantNames =
    isOccupied && current_lease
      ? current_lease.tenants
          .map((t) => `${t.first_name} ${t.last_name}`)
          .join(", ")
      : "";
</script>

<<<<<<< HEAD
<!-- unit card -->
<div class="bg-white p-5 rounded-2xl border border-backdrop flex flex-col gap-8">

  <!-- floor number, unit number, unit status -->
=======
<div
  class="bg-white p-5 rounded-2xl border border-backdrop flex flex-col gap-4"
>
  <!-- FLOOR NUMBER AND UNIT NUMBER -->
>>>>>>> e8a7ccf0ba2f9ea08726b7ba008712e8b1525f33
  <div class="flex flex-col">
    <p class="text-xs font-medium text-muted">
      {floor ? floor : "Unavailable"}
    </p>
    <h3 class="text-lg font-bold font-inter text-teal text-center">
      Unit {unitNumber}
    </h3>
  </div>

<<<<<<< HEAD
  {#if isOccupied}
  <!-- unit status -->
  <button class="flex items-center gap-2 justify-center bg-lightteal p-2 px-10 rounded-xl mt-2">
    <div class="w-2 h-2 bg-teal  rounded-full"></div>
    <p class="text-xs text-teal font-medium">occupied</p> <!-- placeholder -->
  </button>

  <!-- tenant details  -->
  <div class="flex flex-col gap-2">
    <!-- T E N A N T -->
    <p class="text-muted text-xs">Tenant Details</p>
    <!-- tenant name  -->
    <div class="flex items-center gap-2">
      <div class="bg-lightteal text-teal text-xs font-bold rounded-full w-5 h-5 items-center text-center justify-center flex"></div>
      <p class="text-teal text-xs font-medium">{tenantName ? tenantName : "Unavailable"}</p>
    </div>
    
    <!-- tenant member since -->
    <div class="flex gap-2 items-center">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#495F76" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-crown"><path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"/><path d="M5 21h14"/></svg>
      <p class="text-teal text-xs font-medium">November 22 2024</p> <!-- placeholder -->
    </div>

    <hr class="mt-2 mb-2">

    <!-- P A Y M E N T -->
    <p class="text-muted text-xs">Payment Details</p>
    <!-- payment date  -->
    <div class="flex items-center gap-2">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#495F76" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar-clock"><path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3.5"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h5"/><path d="M17.5 17.5 16 16.3V14"/><circle cx="16" cy="16" r="6"/></svg>
      <p class="text-teal text-xs font-medium">
        {formatDate(leaseEndDate ? leaseEndDate : "Not set")}
      </p>
    </div>
    <!-- payment status -->
    <div class="flex gap-2 items-center">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FEBE8C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-loader"><path d="M12 2v4"/><path d="m16.2 7.8 2.9-2.9"/><path d="M18 12h4"/><path d="m16.2 16.2 2.9 2.9"/><path d="M12 18v4"/><path d="m4.9 19.1 2.9-2.9"/><path d="M2 12h4"/><path d="m4.9 4.9 2.9 2.9"/></svg>
      <p class="text-orange text-xs font-medium">Pending</p> <!-- placeholder -->
    </div>

    <hr class="mt-2 mb-2">

    <!-- remarks -->
    <p class="text-muted text-xs">Remarks</p>
    <!-- text area -->
    <div class="flex items-center gap-2">
      <p class="text-teal text-xs font-medium font-dmSans">may otang 5k</p> <!-- placeholder -->
    </div>

  </div>

  {:else}
      <button class="flex items-center gap-2 justify-center bg-green20 p-2 px-10 rounded-xl mt-2">
        <div class="w-2 h-2 bg-green  rounded-full"></div>
        <p class="text-xs text-green font-medium">available</p> <!-- placeholder -->
      </button>
=======
  {#if isOccupied && current_lease}
    <!-- TENANT DETAILS -->
    <div class="flex flex-col gap-2">
      <!-- TENANT COUNT AND NAMES -->
      <div class="flex items-center gap-2">
        <div
          class="bg-lightteal text-teal text-xs font-bold rounded-full w-5 h-5 items-center text-center justify-center flex"
        >
          {current_lease.tenants.length}
        </div>
        <p class="text-teal text-xs font-medium truncate" title={tenantNames}>
          {tenantNames}
        </p>
      </div>

      <!-- LEASE END DATE -->
      <div class="flex gap-2 items-center">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="16"
          height="16"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="text-muted"
        >
          <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
          <line x1="16" x2="16" y1="2" y2="6" />
          <line x1="8" x2="8" y1="2" y2="6" />
          <line x1="3" x2="21" y1="10" y2="10" />
        </svg>
        <p class="text-xs text-muted">
          Lease ends on <b>{formatDate(current_lease.end_date)}</b>
        </p>
      </div>
    </div>
  {:else}
    <!-- IF TENANTS ARE EMPTY, DISPLAY AVAILABLE STATUS -->
    <div
      class="flex items-center gap-2 justify-center bg-green20 p-2 px-4 rounded-xl"
    >
      <div class="w-2 h-2 bg-green rounded-full"></div>
      <p class="text-xs text-green font-medium">Available</p>
    </div>
>>>>>>> e8a7ccf0ba2f9ea08726b7ba008712e8b1525f33
  {/if}
</div>

<style>
  /* HOVER EFFECT */
  .bg-white {
    transition: all 0.2s ease-in-out;
  }

  .bg-white:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
  }
</style>
