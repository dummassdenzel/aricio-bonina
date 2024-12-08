<script lang="ts">
  import { api } from "$lib/services/api";
  import { onMount } from "svelte";
  import TenantFormModal from "$lib/components/manager/tenant-form-modal.svelte";
  import { tenantsStore } from "$lib/stores/tenants-store";

  let units: any[] = [];
  let error: string | null = null;
  let selectedTenant: any = null;

  onMount(async () => {
    try {
      await tenantsStore.loadTenants();
    } catch (err: any) {
      error = err.message;
    }
  });

  // STORE SUBSCRIPTION
  $: tenants = $tenantsStore;

  async function loadUnits() {
    try {
      const response = await api.get("units");
      units = response.payload;
    } catch (err: any) {
      error = err.message;
    }
  }
  // MODAL CONTROLS
  let showModal = false;
  function openModal() {
    showModal = true;
    loadUnits();
  }

  async function closeModal() {
    showModal = false;
  }

  // SELECT TENANT FUNCTION
  function selectTenant(tenant: any) {
    selectedTenant = tenant;
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
              <button
                class="border p-5 w-full rounded-xl items-center justify-between flex hover:bg-back cursor-pointer"
                on:click={() => selectTenant(tenant)}
                class:bg-back={selectedTenant?.id === tenant.id}
              >
                <p class="font-inter text-teal text-sm font-medium">
                  {tenant.first_name}
                  {tenant.last_name}
                </p>
                <p class="font-inter text-slate text-xs font-medium">
                  Unit {tenant.unit_number}
                </p>
              </button>
            {/each}
          </ul>
        {/if}
      </div>
    </div>
  </div>

  <!-- SELECTED TENANT INFORMATION-->
  <div class="flex gap-2 w-full">
    <div class="w-full rounded-2xl border p-6 h-[70vh]">
      {#if selectedTenant}
        <div class="space-y-6 h-full overflow-y-auto">
          <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-teal">Tenant Information</h2>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-muted mb-1">Name</p>
              <p class="text-sm">
                {selectedTenant.first_name}
                {selectedTenant.last_name}
              </p>
            </div>
            <div>
              <p class="text-sm text-muted mb-1">Unit Number</p>
              <p class="text-sm">{selectedTenant.unit_number}</p>
            </div>
            <div>
              <p class="text-sm text-muted mb-1">Email</p>
              <p class="text-sm">{selectedTenant.email}</p>
            </div>
            <div>
              <p class="text-sm text-muted mb-1">Contact Number</p>
              <p class="text-sm">{selectedTenant.contact_number}</p>
            </div>
            <div>
              <p class="text-sm text-muted mb-1">Move-in Date</p>
              <p class="text-sm">
                {new Date(selectedTenant.move_in_date).toLocaleDateString()}
              </p>
            </div>
          </div>

          {#if selectedTenant?.valid_id_url}
            <div>
              <p class="text-sm text-muted mb-2">Valid ID</p>

              {#if selectedTenant.valid_id_type?.toLowerCase().includes("pdf")}
                <div class="border rounded-lg p-4 bg-back">
                  <div class="flex items-center gap-2 mb-2">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="24"
                      height="24"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      class="text-teal"
                      ><path
                        d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"
                      /><polyline points="14 2 14 8 20 8" /></svg
                    >
                    <span class="text-sm">PDF Document</span>
                  </div>
                  <a
                    href={selectedTenant.valid_id_url}
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-sm text-teal hover:underline inline-flex items-center gap-1"
                  >
                    View PDF
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
                      ><path
                        d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"
                      /><polyline points="15 3 21 3 21 9" /><line
                        x1="10"
                        y1="14"
                        x2="21"
                        y2="3"
                      /></svg
                    >
                  </a>
                </div>
              {:else}
                <!-- svelte-ignore a11y_no_noninteractive_element_interactions -->
                <!-- svelte-ignore a11y_click_events_have_key_events -->
                <img
                  src={selectedTenant.valid_id_url}
                  alt="Valid ID"
                  class="max-w-full h-auto rounded-lg border cursor-pointer hover:opacity-90"
                  on:click={() =>
                    window.open(selectedTenant.valid_id_url, "_blank")}
                />
              {/if}
            </div>
          {/if}
        </div>
      {:else}
        <div class="flex items-center justify-center h-full">
          <p class="text-muted text-sm">
            Select a tenant to view their information
          </p>
        </div>
      {/if}
    </div>
  </div>

  <!-- tenant form -->
  <TenantFormModal isOpen={showModal} onClose={closeModal} />
</section>
