<script lang="ts">
  import { api } from "$lib/services/api";
  import { onMount } from "svelte";
  import { load } from "../+layout";

  let tenants: any[] = [];

  let error: string | null = null;
  let success: string | null = null;

  // GET ALL TENANTS
  async function loadTenants() {
    try {
      const response = await api.get("tenants");
      tenants = response.payload;
    } catch (err: any) {
      error = err.message;
    }
  }

  onMount(async () => {
    loadTenants();
  });

  // MODAL CONTROLS
  let showModal = false;
  function openModal() {
    showModal = true;
  }
  function closeModal() {
    showModal = false;
  }

  let formData = {
    first_name: "",
    last_name: "",
    unit_number: "",
    move_in_date: "",
    start_date: "",
    end_date: "",
    rent_amount: "",
  };

  async function handleSubmit(event: SubmitEvent) {
    event.preventDefault();

    try {
      const response = await api.post("addtenant", formData);

      if (response.status.remarks === "success") {
        success = response.status.message;
        closeModal();
        await loadTenants();
      } else {
        error = response.status.message;
      }
    } catch (err: any) {
      error = err.message;
    }
  }
</script>

<h1 class="text-3xl font-bold text-teal">Tenant Management</h1>
<section class="mt-5">
  <div class="flex justify-center gap-2 align-middle items-center">
    <!-- Search Bar -->
    <div class="relative">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#989898" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 transform -translate-y-1/2">
        <circle cx="11" cy="11" r="8" />
        <path d="m21 21-4.3-4.3" />
      </svg>
      <input type="text" placeholder="Search by unit" class="pl-10 text-xs text-dmSans text-muted rounded-2xl p-3.5 bg-back focus:text-teal focus:outline-backdrop" />
    </div>

    <!-- buttons -->
    <div class="flex gap-2">
      <!-- svelte-ignore a11y_consider_explicit_label -->
      <!-- filter button -->
      <button class="bg-back p-3 rounded-2xl">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#989898" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-blend"><circle cx="9" cy="9" r="7" /><circle cx="15" cy="15" r="7" /></svg>
      </button>

      <!-- svelte-ignore a11y_consider_explicit_label -->
      <!-- sort button -->
      <button class="bg-back p-3 rounded-2xl">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#989898" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-down"><path d="m21 16-4 4-4-4" /><path d="M17 20V4" /><path d="m3 8 4-4 4 4"/><path d="M7 4v16" /></svg>
      </button>

      <!-- svelte-ignore a11y_consider_explicit_label -->
      <!-- add unit button -->
      <button class="bg-back p-3 rounded-2xl">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#989898" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus"><path d="M5 12h14" /><path d="M12 5v14" /></svg>
      </button>
      
    </div>
  </div>
</section>












  <!-- LEFT SIDE -->
  <!-- <div class="bg-back rounded-lg p-6">
    <h2 class="text-xl font-bold mb-4">Tenants List</h2>
    {#if error}
      <p class="text-red-500">{error}</p>
    {:else if tenants.length === 0}
      <p>No tenants found.</p>
    {:else}
      <ul class="space-y-2">
        {#each tenants as tenant}
          <li class="p-3 bg-white rounded-lg shadow">
            {tenant.first_name}
            {tenant.last_name}
          </li>
        {/each}
      </ul>
    {/if}
  </div> -->

  <!-- RIGHT SIDE -->
  <!-- <div class="space-y-6"> -->
    <!-- ADD NEW TENANT BUTTON -->
    <!-- <div class="bg-back rounded-lg p-6">
      <button
        on:click={openModal}
        class="w-full bg-muted hover:bg-muted/80 text-white py-3 px-4 rounded-lg transition-colors"
      >
        Add New Tenant
      </button>
    </div> -->

    <!-- PLACEHOLDER -->
    <!-- <div class="bg-back rounded-lg p-6 min-h-[300px]">
      <h2 class="text-xl font-bold mb-4">Highlighted Tenants</h2>
      <p class="text-gray-600">(Display of tenants with outstanding rent)</p>
    </div>
  </div> -->

  <!-- FORM MODAL-->
  <!-- {#if showModal}
    <div
      class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center"
    >
      <div
        class="bg-white p-6 rounded-lg shadow-lg max-h-[80vh] overflow-y-auto"
      >
        <h2 class="text-xl font-bold mb-4">Add New Tenant:</h2>

        <form on:submit|preventDefault={handleSubmit}>
          <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3">Tenant Information</h3>
            <div class="grid grid-cols-2 gap-4">
              <div class="mb-4">
                <label
                  class="block text-gray-700 text-sm font-bold mb-2"
                  for="first_name"
                >
                  First Name
                </label>
                <input
                  type="text"
                  id="first_name"
                  bind:value={formData.first_name}
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                />
              </div>

              <div class="mb-4">
                <label
                  class="block text-gray-700 text-sm font-bold mb-2"
                  for="last_name"
                >
                  Last Name
                </label>
                <input
                  type="text"
                  id="last_name"
                  bind:value={formData.last_name}
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                />
              </div>

              <div class="mb-4">
                <label
                  class="block text-gray-700 text-sm font-bold mb-2"
                  for="unit_number"
                >
                  Unit Number
                </label>
                <input
                  type="text"
                  id="unit_number"
                  bind:value={formData.unit_number}
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                />
              </div>

              <div class="mb-4">
                <label
                  class="block text-gray-700 text-sm font-bold mb-2"
                  for="move_in_date"
                >
                  Move-in Date
                </label>
                <input
                  type="date"
                  id="move_in_date"
                  bind:value={formData.move_in_date}
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                />
              </div>
            </div>
          </div> -->

          <!-- Lease Information Section -->
          <!-- <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3">Lease Information</h3>
            <div class="grid grid-cols-2 gap-4">
              <div class="mb-4">
                <label
                  class="block text-gray-700 text-sm font-bold mb-2"
                  for="start_date"
                >
                  Lease Start Date
                </label>
                <input
                  type="date"
                  id="start_date"
                  bind:value={formData.start_date}
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                />
              </div>

              <div class="mb-4">
                <label
                  class="block text-gray-700 text-sm font-bold mb-2"
                  for="end_date"
                >
                  Lease End Date
                </label>
                <input
                  type="date"
                  id="end_date"
                  bind:value={formData.end_date}
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                />
              </div>

              <div class="mb-4 col-span-2">
                <label
                  class="block text-gray-700 text-sm font-bold mb-2"
                  for="rent_amount"
                >
                  Rent Amount
                </label>
                <input
                  type="number"
                  id="rent_amount"
                  step="0.01"
                  bind:value={formData.rent_amount}
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                />
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-2">
            <button
              type="button"
              class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
              on:click={closeModal}
            >
              Cancel
            </button>
            <button
              type="submit"
              class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
            >
              Save
            </button>
          </div>
        </form>
      </div>
    </div>
  {/if}
</section> -->
