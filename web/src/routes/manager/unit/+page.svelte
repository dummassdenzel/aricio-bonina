<script lang="ts">
  import UnitCard from "$lib/components/manager/unit-card.svelte";
  import { api } from "$lib/services/api";
  import { onMount } from "svelte";

  let isModalOpen: boolean = false;
  let units: any[] = [];
  let filteredUnits: any[] = [];
  let error: string | null = null;
  let selectedFloor: string = "all"; // default (all floors)
  let searchQuery: string = ""; // search query state
  let sortAscending: boolean = true; // sort state: true = ascending, false = descending

  onMount(async () => {
    try {
      const response = await api.get("units");
      units = response.payload;
      filterUnits();
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

<!-- F R O N T E N D -->
<h1 class="text-3xl font-bold text-teal">Unit Management</h1>

<section class="mt-6">
  <div class="flex justify-between align-middle items-center">
    <!-- floor navigation -->
    <div class="bg-back p-1 rounded-xl w-max flex"> 
      <button
      class="p-2 py-3 w-16 text-xs font-semibold text-muted rounded-lg transition"
      class:bg-lightteal={selectedFloor === "all"}
      class:text-teal={selectedFloor === "all"}
      on:click={() => handleFloorClick("all")}>
      All
    </button>

    {#each [1, 2, 3, 4, 5] as floor}
    <button
      class="p-2 w-16 text-xs font-semibold text-muted rounded-lg transition"
      class:bg-lightteal={selectedFloor === String(floor)}
      class:text-teal={selectedFloor === String(floor)}
      on:click={() => handleFloorClick(String(floor))}>
      {floor}
    </button>
    {/each}
    </div>

    <!-- functionality -->
    <div class="flex gap-2">
      <!-- search bar -->
      <div class="relative">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#989898" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 transform -translate-y-1/2"><circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" /></svg>
        <input type="text" placeholder="Search by unit or tenant name" class="pl-10 text-xs text-dmSans w-72 text-muted rounded-2xl p-3.5 bg-back focus:text-teal focus:outline-backdrop"
          on:input={handleSearchInput} 
        /> <!-- search bar functionality -->
      </div>

      <!-- buttons -->
      <div class="flex gap-2">
        <!-- svelte-ignore a11y_consider_explicit_label -->
        <!-- filter button -->
        <button class="bg-back p-3 rounded-2xl">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"  viewBox="0 0 24 24" fill="none" stroke="#989898" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-blend"><circle cx="9" cy="9" r="7" /><circle cx="15" cy="15" r="7" /></svg>
        </button>

        <!-- svelte-ignore a11y_consider_explicit_label -->
        <!-- sort button -->
        <button class="bg-back p-3 rounded-2xl" on:click={toggleSortOrder}>
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#989898" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-down"><path d="m21 16-4 4-4-4" /><path d="M17 20V4" /><path d="m3 8 4-4 4 4"/><path d="M7 4v16" /></svg>
        </button>
      </div>
    </div>
  </div>

  <!-- unit cards -->
  <div class="mt-8 rounded-xl p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 overflow-auto max-h-[500px] scrollbar-none">
    {#if error}
      <p class="text-red-500">{error}</p>
    {:else if filteredUnits.length === 0}
      <p class="text-sm text-muted font-medium">No units or tenants found.</p>
    {:else}
      {#each filteredUnits as unit}
        <UnitCard
          unitNumber={unit.unit_number}
          floor={unit.floor.toString()}
          current_lease={unit.current_lease}
          isOverdue={unit.current_lease?.end_date <
            new Date().toISOString().split("T")[0]}
        />
      {/each}
    {/if}
  </div>
</section>
