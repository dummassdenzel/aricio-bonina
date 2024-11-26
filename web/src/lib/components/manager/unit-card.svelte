<script lang="ts">
  import { formatDate } from "$lib/pipes/date-pipe";
  import UnitModal from "./unit-modal.svelte";

  export let unitNumber: string;
  export let floor: string;
  export let current_lease: {
    tenants: Array<{
      first_name: string;
      last_name: string;
      email: string;
      phone: string;
    }>;
    end_date: string;
    start_date: string;
    rent_amount: number;
  } | null;

  let isModalOpen = false;

  $: isOccupied = current_lease !== null;
  $: tenantNames =
    isOccupied && current_lease
      ? current_lease.tenants
          .map((t) => `${t.first_name} ${t.last_name}`)
          .join(", ")
      : "";

  const handleOpenModal = () => {
    isModalOpen = true;
  };

  const handleCloseModal = () => {
    isModalOpen = false;
  };
</script>

<button
  on:click={handleOpenModal}
  class="bg-white p-5 rounded-2xl border border-backdrop flex flex-col gap-4"
>
  <!-- FLOOR NUMBER AND UNIT NUMBER -->
  <div class="flex flex-col">
    <p class="text-xs font-medium text-muted">
      {floor ? floor : "Unavailable"}
    </p>
    <h3 class="text-lg font-bold font-inter text-teal text-center">
      Unit {unitNumber}
    </h3>
  </div>

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
  {/if}
</button>

<UnitModal
  isOpen={isModalOpen}
  onClose={handleCloseModal}
  unit={{
    unit_number: parseInt(unitNumber),
    floor: parseInt(floor),
    current_lease: current_lease || undefined,
  }}
/>

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
