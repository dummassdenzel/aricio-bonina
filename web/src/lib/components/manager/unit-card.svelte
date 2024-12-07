<script lang="ts">
  import { formatDate } from "$lib/pipes/date-pipe";
  import UnitModal from "./unit-modal.svelte";

  export let unitNumber: string;
  export let current_lease: {
    id: number;
    tenants: Array<{
      first_name: string;
      last_name: string;
      move_in_date: string;
    }>;
    end_date: string;
    start_date: string;
    rent_amount: number;
  } | null;
  export let isOverdue: boolean;

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
  class="modal p-5 rounded-2xl border flex flex-col gap-4 relative"
  on:click={handleOpenModal}
>
  <!-- unit number and floor number -->
  <div class="flex items-center justify-between w-full">
    <div class="flex gap-2 items-center">
      <h3 class="text-lg font-bold font-inter text-teal text-center">
        Unit {unitNumber}
      </h3>
      <!-- <span class="text-xs font-medium text-muted">·</span>
      <p class="text-xs font-medium text-muted">
        F{floor ? floor : "Unavailable"}
      </p> -->
    </div>
    <div class="flex gap-2">
      {#if isOverdue}
        <div class="p-1 bg-red20 rounded-full">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="15"
            height="15"
            viewBox="0 0 24 24"
            fill="none"
            stroke="#ef4444"
            stroke-width="1.5"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="lucide lucide-clock-alert"
            ><path d="M12 6v6l4 2" /><path
              d="M16 21.16a10 10 0 1 1 5-13.516"
            /><path d="M20 11.5v6" /><path d="M20 21.5h.01" /></svg
          >
        </div>
      {/if}
    </div>
  </div>

  {#if isOccupied && current_lease}
    <!-- tenant overview -->
    <div class="flex flex-col gap-2">
      <!-- tenant count and tenant names -->
      <div class="flex items-center gap-2">
        <div
          class="bg-lightteal text-teal text-xs font-bold rounded-full w-5 h-5 items-center text-center justify-center flex"
        >
          {current_lease.tenants.length}
        </div>

        <p class="text-teal text-xs font-medium truncate" title={tenantNames}>
          {current_lease.tenants.length > 1
            ? `${current_lease.tenants[0].first_name} ${current_lease.tenants[0].last_name}, +${current_lease.tenants.length - 1} more`
            : tenantNames}
        </p>
      </div>

      <!-- lease overview -->
      <div class="flex gap-2">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="15"
          height="15"
          viewBox="0 0 24 24"
          fill="none"
          stroke="#314A60"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
          <line x1="16" x2="16" y1="2" y2="6" /><line
            x1="8"
            x2="8"
            y1="2"
            y2="6"
          /><line x1="3" x2="21" y1="10" y2="10" /></svg
        >
        <p class="text-xs text-muted">
          Lease ends on <b>{formatDate(current_lease.end_date)}</b>
        </p>
      </div>
    </div>
  {:else}
    <!-- IF TENANTS ARE EMPTY, DISPLAY AVAILABLE STATUS -->
    <div
      class="flex items-center gap-2 justify-center bg-green20 p-2 w-full rounded-lg mt-3"
    >
      <div class="w-2 h-2 bg-green rounded-full"></div>
      <p class="text-xs text-green font-medium">Available</p>
    </div>
  {/if}
</button>

<UnitModal
  isOpen={isModalOpen}
  onClose={handleCloseModal}
  unitNumber={parseInt(unitNumber)}
/>

<style>
  .modal {
    transition: all 0.3s ease-in-out;
  }

  .modal:hover {
    transform: translateY(-4px);
    box-shadow:
      0 6px 12px rgba(194, 192, 192, 0.3),
      0 4px 10px rgba(194, 192, 192, 0.08);
  }
</style>
