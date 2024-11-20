<script lang="ts">
  import UnitCard from "$lib/components/manager/unit-card.svelte";
  import { api } from "$lib/services/api";
  import { onMount } from "svelte";

  let units: any = [];
  let error: string | null = null;

  onMount(async () => {
    // GET ALL UNITS
    try {
      const response = await api.get("units");
      units = response.payload;
    } catch (err: any) {
      error = err.message;
    }
  });
</script>

<h1 class="text-3xl font-bold text-teal">Unit Management</h1>
<!-- ignore this -->

<section class=" mt-5">
  <!-- action buttons container -->
  <div class="flex justify-center gap-2 align-middle items-center">
    <!-- search bar -->
    <div class="relative">
      <!-- search icon -->
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="20"
        height="20"
        viewBox="0 0 24 24"
        fill="none"
        stroke="#989898"
        stroke-width="1"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="absolute left-3 top-1/2 transform -translate-y-1/2"
      >
        <circle cx="11" cy="11" r="8" />
        <path d="m21 21-4.3-4.3" />
      </svg>
      <!-- input field: type search, text for now -->
      <input
        type="text"
        placeholder="Search by unit"
        class="pl-10 text-xs text-dmSans text-muted rounded-2xl p-3.5 bg-back focus:text-teal focus:outline-backdrop"
      />
    </div>

    <!-- buttons -->
    <div class="flex gap-2">
      <!-- svelte-ignore a11y_consider_explicit_label -->
      <!-- filter button -->
      <button class="bg-back p-3 rounded-2xl">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="20"
          height="20"
          viewBox="0 0 24 24"
          fill="none"
          stroke="#989898"
          stroke-width="1"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="lucide lucide-blend"
          ><circle cx="9" cy="9" r="7" /><circle cx="15" cy="15" r="7" /></svg
        >
      </button>

      <!-- svelte-ignore a11y_consider_explicit_label -->
      <!-- sort button -->
      <button class="bg-back p-3 rounded-2xl">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="20"
          height="20"
          viewBox="0 0 24 24"
          fill="none"
          stroke="#989898"
          stroke-width="1"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="lucide lucide-arrow-up-down"
          ><path d="m21 16-4 4-4-4" /><path d="M17 20V4" /><path
            d="m3 8 4-4 4 4"
          /><path d="M7 4v16" /></svg
        >
      </button>

      <!-- svelte-ignore a11y_consider_explicit_label -->
      <!-- add unit button -->
      <button class="bg-back p-3 rounded-2xl">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="20"
          height="20"
          viewBox="0 0 24 24"
          fill="none"
          stroke="#989898"
          stroke-width="1"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="lucide lucide-plus"
          ><path d="M5 12h14" /><path d="M12 5v14" /></svg
        >
      </button>
    </div>
  </div>

  <!-- cards -->
  <div
    class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 overflow-auto max-h-[550px] scrollbar-none"
  >
    {#if error}
      <p class="text-red-500">{error}</p>
    {:else if units.length === 0}
      <p>No units found.</p>
    {:else}
      {#each units as unit}
        <UnitCard
          unitNumber={unit.unit_number}
          floor={unit.floor}
          tenantName={unit.first_name && unit.last_name
            ? `${unit.first_name} ${unit.last_name}`
            : "Unoccupied"}
          leaseEndDate={unit.end_date}
        />
      {/each}
    {/if}
  </div>
</section>
