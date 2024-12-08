<script lang="ts">
  import { api } from "$lib/services/api";
  import { onMount } from "svelte";
  import TenantFormModal from "$lib/components/manager/tenant-form-modal.svelte";

  let tenants: any[] = [];
  let units: any[] = [];

  let error: string | null = null;

  // GET ALL TENANTS
  async function loadTenants() {
    try {
      const response = await api.get("tenants");
      tenants = response.payload;
    } catch (err: any) {
      error = err.message;
    }
  }

  async function loadUnits() {
    try {
      const response = await api.get("units");
      units = response.payload;
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
    loadUnits();
  }

  async function closeModal() {
    showModal = false;
  }
</script>

<h1 class="text-3xl font-bold text-teal">Tenant Management</h1>
<section class="grid grid-cols-2 gap-8 mt-6">
  <div class="flex justify-between gap-2 w-full">
    <!-- tenant list -->

    <div class="w-full h-[70vh] flex flex-col">
      <!-- functionality -->
      <div class="flex gap-2 mb-6">
        <!-- search bar -->
        <div class="flex items-center relative">
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
            ><circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" /></svg
          >
          <input
            type="text"
            placeholder="Search by unit or tenant name"
            class="pl-10 text-xs text-dmSans w-72 text-muted rounded-2xl p-3.5 bg-back focus:text-teal focus:outline-backdrop"
          />
          <!-- search bar functionality -->
        </div>

        <!-- buttons -->
        <div class="flex gap-2">
          <!-- svelte-ignore a11y_consider_explicit_label -->
          <!-- sort button -->
          <button class="bg-back p-3 rounded-2xl hover:bg-backdrop">
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
          <!-- add button -->
          <button
            class="bg-back p-3 rounded-2xl hover:bg-backdrop"
            on:click={openModal}
          >
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

      <div class="max-h-full overflow-y-auto flex flex-col gap-2">
        {#if error}
          <p class="text-red-500">{error}</p>
        {:else if tenants.length === 0}
          <div
            class="border p-5 rounded-xl items-center justify-between flex bg-back"
          >
            <p class="text-sm text-muted">No tenants found.</p>
          </div>
        {:else}
          <ul class="space-y-2">
            {#each tenants as tenant}
              <div
                class="border p-5 rounded-xl items-center justify-between flex"
              >
                <p class="font-inter text-teal text-sm font-medium">
                  {tenant.first_name}
                  {tenant.last_name}
                </p>
                <p class="font-inter text-slate text-xs font-medium">
                  Unit {tenant.unit_number}
                </p>
              </div>
            {/each}
          </ul>
        {/if}
      </div>
    </div>
  </div>

  <!-- highlight tenant -->
  <div class="flex gap-2 w-full">
    <div class="w-full rounded-2xl border"></div>
    <div class="w-full rounded-2xl border"></div>
  </div>

  <!-- tenant form -->
  <TenantFormModal isOpen={showModal} onClose={closeModal} />
</section>
