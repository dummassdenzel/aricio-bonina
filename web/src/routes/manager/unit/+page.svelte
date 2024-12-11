<script lang="ts">
  import UnitCard from "$lib/components/manager/unit-card.svelte";
  import { api } from "$lib/services/api";
  import { onMount } from "svelte";
  import { unitsStore } from "$lib/stores/units-store";

  let isModalOpen: boolean = false;
  let units: any[] = [];
  let filteredUnits: any[] = [];
  let error: string | null = null;
  let selectedFloor: string = "all"; // default (all floors)
  let searchQuery: string = ""; // search query state
  let sortAscending: boolean = true; // sort state: true = ascending, false = descending

  // Subscribe to the store
  $: units = $unitsStore;

  $: {
    if (units) {
      filterUnits();
    }
  }

  onMount(async () => {
    try {
      await unitsStore.loadUnits();
    } catch (err: any) {
      error = err.message;
    }
  });

  // unit modal
  const toggleModal = () => {
    isModalOpen = !isModalOpen;
  };

  // filter units based on floor and search query
  const filterUnits = () => {
    let filtered = units;

    // filter by floor
    if (selectedFloor !== "all") {
      filtered = filtered.filter(
        (unit) => unit.floor === parseInt(selectedFloor),
      );
    }

    // filter by search query (unit number or tenant names)
    if (searchQuery.trim() !== "") {
      const searchLower = searchQuery.toLowerCase();

      filtered = filtered.filter((unit) => {
        // check if the unit number matches
        const matchesUnitNumber = unit.unit_number
          .toString()
          .toLowerCase()
          .includes(searchLower);

        // check if any tenant's name matches (if occupied)
        const matchesTenantName = unit.current_lease?.tenants?.some(
          (tenant: { first_name: any; last_name: any }): boolean => {
            const fullName =
              `${tenant.first_name} ${tenant.last_name}`.toLowerCase();
            return fullName.includes(searchLower);
          },
        );

        return matchesUnitNumber || matchesTenantName;
      });
    }

    // apply sorting
    filtered.sort((a, b) =>
      sortAscending
        ? a.unit_number - b.unit_number
        : b.unit_number - a.unit_number,
    );

    filteredUnits = filtered;
  };

  // for floor button functionality
  const handleFloorClick = (floor: string) => {
    selectedFloor = floor;
    filterUnits();
  };

  // for search bar functionality
  const handleSearchInput = (event: Event) => {
    searchQuery = (event.target as HTMLInputElement).value;
    filterUnits();
  };

  // toggle sort order
  const toggleSortOrder = () => {
    sortAscending = !sortAscending;
    filterUnits(); // reapply filters and sorting
  };
</script>

<div class="">
  <div class="flex flex-col gap-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
      <h1 class="text-2xl sm:text-3xl font-bold text-teal">Unit Management</h1>

      <!-- Stats Cards -->
      <div class="grid grid-cols-3 gap-4 w-full sm:w-auto">
        <div class="bg-white rounded-lg p-4 shadow-sm">
          <p class="text-xs text-muted mb-1">Total Units</p>
          <p class="text-lg font-bold text-teal">{filteredUnits.length}</p>
        </div>
        <div class="bg-white rounded-lg p-4 shadow-sm">
          <p class="text-xs text-muted mb-1">Occupied</p>
          <p class="text-lg font-bold text-teal">
            {filteredUnits.filter((u) => u.current_lease).length}
          </p>
        </div>
        <div class="bg-white rounded-lg p-4 shadow-sm">
          <p class="text-xs text-muted mb-1">Vacant</p>
          <p class="text-lg font-bold text-teal">
            {filteredUnits.filter((u) => !u.current_lease).length}
          </p>
        </div>
      </div>
    </div>

    <!-- Controls Section -->
    <div class="flex flex-col sm:flex-row justify-between gap-4">
      <!-- Floor Navigation -->
      <div class="bg-back p-1 rounded-xl w-full sm:w-auto overflow-x-auto">
        <div class="flex min-w-max">
          <button
            class="p-2 py-3 w-16 text-xs font-semibold rounded-lg transition-colors"
            class:bg-lightteal={selectedFloor === "all"}
            class:text-teal={selectedFloor === "all"}
            class:text-muted={selectedFloor !== "all"}
            on:click={() => handleFloorClick("all")}
          >
            All
          </button>

          {#each [1, 2, 3, 4, 5] as floor}
            <button
              class="p-2 w-16 text-xs font-semibold rounded-lg transition-colors"
              class:bg-lightteal={selectedFloor === String(floor)}
              class:text-teal={selectedFloor === String(floor)}
              class:text-muted={selectedFloor !== String(floor)}
              on:click={() => handleFloorClick(String(floor))}
            >
              {floor}
            </button>
          {/each}
        </div>
      </div>

      <!-- Search and Actions -->
      <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
        <!-- Search -->
        <div class="relative flex-grow sm:flex-grow-0">
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
          <input
            type="text"
            placeholder="Search by unit or tenant name"
            class="w-full sm:w-72 pl-10 text-xs text-dmSans text-muted rounded-2xl p-3.5 bg-back focus:text-teal focus:outline-backdrop"
            on:input={handleSearchInput}
          />
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-2 justify-end sm:justify-start">
          <button
            class="bg-back p-3 rounded-2xl hover:bg-slate/5 transition-colors group"
            aria-label="Sort units"
            on:click={toggleSortOrder}
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="#989898"
              class="group-hover:stroke-teal transition-colors"
              stroke-width="1"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <path d="m21 16-4 4-4-4" />
              <path d="M17 20V4" />
              <path d="m3 8 4-4 4 4" />
              <path d="M7 4v16" />
            </svg>
          </button>

          <button
            class="bg-back p-3 rounded-2xl hover:bg-slate/5 transition-colors group"
            aria-label="Filter units"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="#989898"
              class="group-hover:stroke-teal transition-colors"
              stroke-width="1"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <circle cx="9" cy="9" r="7" />
              <circle cx="15" cy="15" r="7" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Units Grid -->
    <div class="mt-2">
      {#if error}
        <div class="bg-red20 text-red p-4 rounded-xl">
          <p class="text-sm">{error}</p>
        </div>
      {:else if filteredUnits.length === 0}
        <div class="bg-back rounded-xl p-8 text-center">
          <p class="text-sm text-muted font-medium">
            No units found matching your criteria.
          </p>
        </div>
      {:else}
        <div
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 overflow-y-auto max-h-[calc(100vh-280px)] scrollbar-thin scrollbar-thumb-slate/20 scrollbar-track-transparent p-1"
        >
          {#each filteredUnits as unit}
            <UnitCard
              unitNumber={unit.unit_number}
              current_lease={unit.current_lease}
              isOverdue={unit.current_lease?.end_date <
                new Date().toISOString().split("T")[0]}
            />
          {/each}
        </div>
      {/if}
    </div>
  </div>
</div>
