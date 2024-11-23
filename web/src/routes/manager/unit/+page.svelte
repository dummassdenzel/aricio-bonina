<script lang="ts">
  import UnitCard from "$lib/components/manager/unit-card.svelte";
  import { api } from "$lib/services/api";
  import { onMount } from "svelte";

  interface Tenant {
    id: number;
    first_name: string;
    last_name: string;
    move_in_date: string;
  }

  interface Lease {
    id: number;
    start_date: string;
    end_date: string;
    date_renewed: string | null;
    rent_amount: number;
    tenants: Tenant[];
  }

  interface Unit {
    id: number;
    unit_number: string;
    floor: number;
    price: number;
    max_occupants: number;
    unit_furniture: string;
    unit_description: string;
    image_url: string;
    status: "occupied" | "vacant";
    current_lease: Lease | null;
  }

  let isModalOpen: boolean = false;
  let units: Unit[] = [];
  let filteredUnits: Unit[] = [];
  let error: string | null = null;
<<<<<<< HEAD
<<<<<<< HEAD
  let selectedFloor: string = "all"; // default (all floors)
  let searchQuery: string = ""; // search query state
  let sortAscending: boolean = true; // sort state: true = ascending, false = descending
=======
  let selectedFloor: string = "all";
  let searchQuery: string = "";
>>>>>>> e8a7ccf0ba2f9ea08726b7ba008712e8b1525f33
=======
  let selectedFloor: string = "all"; // default (all floor)
>>>>>>> parent of a8b7df5 (unit search and sort (done))

  onMount(async () => {
    // fetching units
    try {
      const response = await api.get("units");
      units = response.payload;
      filterUnits();
    } catch (err: any) {
      error = err.message;
    }
  });

  // modal
  const toggleModal = () => {
    isModalOpen = !isModalOpen;
  };

  // filter of units
  const filterUnits = () => {
<<<<<<< HEAD
<<<<<<< HEAD
    let filtered = units;

    // filter by floor
    if (selectedFloor !== "all") {
      filtered = filtered.filter((unit) => unit.floor === parseInt(selectedFloor));
    }

    // filter by search query (unit number)
    if (searchQuery.trim() !== "") {
      filtered = filtered.filter((unit) =>
        unit.unit_number.toString().toLowerCase().includes(searchQuery.toLowerCase())
      );
    }

    // apply sorting
    filtered.sort((a, b) =>
      sortAscending
        ? a.unit_number - b.unit_number
        : b.unit_number - a.unit_number
    );

=======
    let filtered = [...units];

    // Floor filter
    if (selectedFloor !== "all") {
      filtered = filtered.filter(
        (unit) => unit.floor === parseInt(selectedFloor),
      );
    }

    // Search filter
    if (searchQuery) {
      filtered = filtered.filter((unit) => {
        const searchLower = searchQuery.toLowerCase();

        // Search by unit number
        if (unit.unit_number.toLowerCase().includes(searchLower)) {
          return true;
        }

        // Search by tenant names if unit is occupied
        if (unit.current_lease?.tenants) {
          return unit.current_lease.tenants.some((tenant) =>
            `${tenant.first_name} ${tenant.last_name}`
              .toLowerCase()
              .includes(searchLower),
          );
        }

        return false;
      });
    }

>>>>>>> e8a7ccf0ba2f9ea08726b7ba008712e8b1525f33
    filteredUnits = filtered;
=======
    if (selectedFloor === "all") {
      filteredUnits = units;
    } else {
      filteredUnits = units.filter((unit) => unit.floor === parseInt(selectedFloor));
    }
>>>>>>> parent of a8b7df5 (unit search and sort (done))
  };

  // floor button
  const handleFloorClick = (floor: string) => {
    selectedFloor = floor;
    filterUnits();
  };
<<<<<<< HEAD

<<<<<<< HEAD
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
=======
  // Handle search input
  const handleSearch = (event: Event) => {
    searchQuery = (event.target as HTMLInputElement).value;
    filterUnits();
  };
>>>>>>> e8a7ccf0ba2f9ea08726b7ba008712e8b1525f33
=======
>>>>>>> parent of a8b7df5 (unit search and sort (done))
</script>

<h1 class="text-3xl font-bold text-teal">Unit Management</h1>

<section class="mt-5">
  <div class="flex justify-center gap-2 align-middle items-center">
    <!-- Search Bar -->
    <div class="relative">
<<<<<<< HEAD
<<<<<<< HEAD
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#989898" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 transform -translate-y-1/2"><circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" /></svg>
      <input type="text" placeholder="Search by unit" class="pl-10 text-xs text-dmSans text-muted rounded-2xl p-3.5 bg-back focus:text-teal focus:outline-backdrop"
        on:input={handleSearchInput} /> <!-- search bar functionality -->
=======
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
        class="pl-10 text-xs text-dmSans text-muted rounded-2xl p-3.5 bg-back focus:text-teal focus:outline-backdrop"
        on:input={handleSearch}
        value={searchQuery}
      />
>>>>>>> e8a7ccf0ba2f9ea08726b7ba008712e8b1525f33
=======
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#989898" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 transform -translate-y-1/2">
        <circle cx="11" cy="11" r="8" />
        <path d="m21 21-4.3-4.3" />
      </svg>
      <input type="text" placeholder="Search by unit" class="pl-10 text-xs text-dmSans text-muted rounded-2xl p-3.5 bg-back focus:text-teal focus:outline-backdrop" />
>>>>>>> parent of a8b7df5 (unit search and sort (done))
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
<<<<<<< HEAD
<<<<<<< HEAD
      <button class="bg-back p-3 rounded-2xl" on:click={toggleSortOrder}>
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#989898" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-down">
          <path d="m21 16-4 4-4-4" /><path d="M17 20V4" />
          <path d="m3 8 4-4 4 4" /><path d="M7 4v16" />
        </svg>
=======
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
>>>>>>> e8a7ccf0ba2f9ea08726b7ba008712e8b1525f33
=======
      <button class="bg-back p-3 rounded-2xl">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#989898" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-down"><path d="m21 16-4 4-4-4" /><path d="M17 20V4" /><path d="m3 8 4-4 4 4"/><path d="M7 4v16" /></svg>
>>>>>>> parent of a8b7df5 (unit search and sort (done))
      </button>
    </div>
  </div>

  <!-- floor navigation -->
  <div class="flex justify-center mt-5 gap-4">
    <button
      class="px-4 py-2 text-sm font-semibold text-teal rounded-full transition"
      class:bg-lightteal={selectedFloor === "all"}
      on:click={() => handleFloorClick("all")}>All Floors</button
    >

    {#each [1, 2, 3, 4, 5] as floor}
      <button
        class="px-5 py-2 text-sm font-semibold text-teal rounded-full transition"
        class:bg-lightteal={selectedFloor === String(floor)}
        on:click={() => handleFloorClick(String(floor))}
      >
        {floor}
      </button>
    {/each}
    
  </div>

  <!-- unit cards -->
  <div
    class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 overflow-auto max-h-[490px] scrollbar-none"
  >
    {#if error}
      <p class="text-red-500">{error}</p>
    {:else if filteredUnits.length === 0}
      <p>No units found.</p>
    {:else}
      {#each filteredUnits as unit}
        <UnitCard
          unitNumber={unit.unit_number}
          floor={unit.floor.toString()}
          current_lease={unit.current_lease}
        />
      {/each}
    {/if}
  </div>
</section>