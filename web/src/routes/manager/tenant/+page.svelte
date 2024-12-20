<script lang="ts">
  import { api } from "$lib/services/api";
  import { onMount } from "svelte";
  import TenantFormModal from "$lib/components/manager/tenant-form-modal.svelte";
  import { tenantsStore } from "$lib/stores/tenants-store";
  import Swal from "sweetalert2";

  let units: any[] = [];
  let error: string | null = null;
  let selectedTenant: any = null;
  let searchQuery = "";
  let filterStatus: "all" | "active" | "expired" = "all";
  let searchTimeout: NodeJS.Timeout;

  // Debounced search
  $: {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
      try {
        await tenantsStore.loadTenants({
          search: searchQuery || undefined,
          status: filterStatus !== "all" ? filterStatus : undefined,
        });
      } catch (err: any) {
        error = err.message;
      }
    }, 300);
  }

  onMount(() => {
    tenantsStore.loadTenants();
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

  async function handleValidIdUpload(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (!file || !selectedTenant) return;

    // Validate file type
    const allowedTypes = [
      "image/jpeg",
      "image/png",
      "image/webp",
      "application/pdf",
    ];
    if (!allowedTypes.includes(file.type)) {
      await Swal.fire({
        title: "Invalid File Type",
        text: "Please upload a JPG, PNG, WebP image or PDF file",
        icon: "error",
      });
      input.value = "";
      return;
    }

    // Validate file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
      await Swal.fire({
        title: "File Too Large",
        text: "File size should be less than 5MB",
        icon: "error",
      });
      input.value = "";
      return;
    }

    try {
      const formData = new FormData();
      formData.append("tenant_id", selectedTenant.id);
      formData.append("valid_id", file);

      const response = await api.postFormData("updateValidId", formData);

      if (response.status.remarks === "success") {
        // Update the tenant's valid_id_url in the UI
        selectedTenant.valid_id_url = response.payload.valid_id_url;
        selectedTenant.valid_id_type = file.type;

        await Swal.fire({
          title: "Success!",
          text: "Valid ID updated successfully",
          icon: "success",
        });
      }
    } catch (err: any) {
      await Swal.fire({
        title: "Error",
        text: err.message || "Failed to update valid ID",
        icon: "error",
      });
    } finally {
      input.value = ""; // Reset input
    }
  }
</script>

<div class="">
  <div class="flex flex-col gap-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between flex-col sm:flex-row gap-4">
      <h1 class="text-2xl sm:text-3xl font-bold text-teal">
        Tenant Management
      </h1>

      <!-- Stats Cards -->
      <div
        class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-4 w-full sm:w-auto"
      >
        <div class="bg-white rounded-lg p-3 sm:p-4 shadow-sm">
          <p class="text-[10px] sm:text-xs text-muted mb-1">Total Tenants</p>
          <p class="text-base sm:text-lg font-bold text-teal">
            {tenants.length}
          </p>
        </div>
        <div class="bg-white rounded-lg p-3 sm:p-4 shadow-sm">
          <p class="text-[10px] sm:text-xs text-muted mb-1">Active</p>
          <p class="text-base sm:text-lg font-bold text-teal">
            {tenants.filter(
              (t) =>
                !t.current_lease?.end_date ||
                new Date(t.current_lease.end_date).setHours(0, 0, 0, 0) >=
                  new Date().setHours(0, 0, 0, 0),
            ).length}
          </p>
        </div>
        <div class="bg-white rounded-lg p-3 sm:p-4 shadow-sm">
          <p class="text-[10px] sm:text-xs text-muted mb-1">Overdue</p>
          <p class="text-base sm:text-lg font-bold text-red">
            {tenants.filter(
              (t) =>
                t.current_lease?.end_date &&
                new Date(t.current_lease.end_date).setHours(0, 0, 0, 0) <
                  new Date().setHours(0, 0, 0, 0),
            ).length}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Tenant List Section -->
      <div class="flex flex-col">
        <!-- Search and Actions -->
        <div class="flex flex-col sm:flex-row gap-3 mb-6">
          <!-- Search -->
          <div class="relative flex-grow">
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
              placeholder="Search by name, unit, email or contact"
              bind:value={searchQuery}
              class="w-full pl-10 text-xs text-dmSans text-muted rounded-2xl p-3.5 bg-back focus:text-teal focus:outline-backdrop"
            />
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-2 justify-end sm:justify-start">
            <select
              bind:value={filterStatus}
              class="bg-back rounded-2xl px-4 py-2 text-xs text-slate"
            >
              <option value="all">All Tenants</option>
              <option value="active">Active Only</option>
              <option value="expired">Overdue Only</option>
            </select>

            <button
              class="bg-back p-3 rounded-2xl hover:bg-slate/5 transition-colors group"
              on:click={openModal}
              aria-label="Add tenant"
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
                <path d="M5 12h14" />
                <path d="M12 5v14" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Tenant List -->
        <div class="flex-grow overflow-y-auto max-h-[calc(100vh-280px)] pr-2">
          {#if error}
            <div class="bg-red20 text-red p-4 rounded-xl">
              <p class="text-sm">{error}</p>
            </div>
          {:else if tenants.length === 0}
            <div class="border p-5 rounded-xl bg-back text-center">
              <p class="text-sm text-muted">No tenants found.</p>
            </div>
          {:else}
            <div class="space-y-2">
              {#each tenants as tenant}
                <button
                  class="w-full border p-5 rounded-xl flex items-center justify-between hover:bg-back transition-colors"
                  class:bg-back={selectedTenant?.id === tenant.id}
                  on:click={() => selectTenant(tenant)}
                >
                  <div
                    class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3"
                  >
                    <p class="font-inter text-teal text-sm font-medium">
                      {tenant.first_name}
                      {tenant.last_name}
                    </p>
                    <span class="text-xs text-slate"
                      >Unit {tenant.unit_number}</span
                    >
                  </div>
                  <div class="flex items-center gap-2">
                    {#if tenant.current_lease}
                      {#if new Date(tenant.current_lease.end_date).setHours(0, 0, 0, 0) < new Date().setHours(0, 0, 0, 0)}
                        <span
                          class="text-xs text-red bg-red20 px-2 py-1 rounded-full"
                          >Overdue</span
                        >
                      {:else}
                        <span
                          class="text-xs text-green bg-green20 px-2 py-1 rounded-full"
                          >Active</span
                        >
                      {/if}
                      <span class="text-xs text-slate">
                        Until {new Date(
                          tenant.current_lease.end_date,
                        ).toLocaleDateString()}
                      </span>
                    {:else}
                      <span
                        class="text-xs text-orange bg-orange20 px-2 py-1 rounded-full"
                        >No Active Lease</span
                      >
                    {/if}
                  </div>
                </button>
              {/each}
            </div>
          {/if}
        </div>
      </div>

      <!-- Tenant Details Section -->
      <div class="w-full h-full">
        <div class="border rounded-2xl h-full">
          {#if selectedTenant}
            <div class="p-6 space-y-6 h-full overflow-y-auto">
              <div class="flex justify-between items-center border-b pb-4">
                <h2 class="text-lg sm:text-xl font-bold text-teal">
                  Tenant Information
                </h2>
                <div class="flex items-center gap-2">
                  {#if selectedTenant.current_lease?.end_date && new Date(selectedTenant.current_lease.end_date).setHours(0, 0, 0, 0) < new Date().setHours(0, 0, 0, 0)}
                    <span
                      class="text-xs text-red bg-red20 px-3 py-1.5 rounded-full"
                      >Overdue</span
                    >
                  {:else}
                    <span
                      class="text-xs text-green bg-green20 px-3 py-1.5 rounded-full"
                      >Active</span
                    >
                  {/if}
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="space-y-1">
                  <p class="text-xs text-muted">Name</p>
                  <p class="text-sm font-medium text-slate">
                    {selectedTenant.first_name}
                    {selectedTenant.last_name}
                  </p>
                </div>
                <div class="space-y-1">
                  <p class="text-xs text-muted">Unit Number</p>
                  <p class="text-sm font-medium text-slate">
                    Unit {selectedTenant.unit_number}
                  </p>
                </div>
                <div class="space-y-1">
                  <p class="text-xs text-muted">Email</p>
                  <p class="text-sm font-medium text-slate">
                    {selectedTenant.email}
                  </p>
                </div>
                <div class="space-y-1">
                  <p class="text-xs text-muted">Contact Number</p>
                  <p class="text-sm font-medium text-slate">
                    {selectedTenant.contact_number}
                  </p>
                </div>
                <div class="space-y-1">
                  <p class="text-xs text-muted">Move-in Date</p>
                  <p class="text-sm font-medium text-slate">
                    {new Date(selectedTenant.move_in_date).toLocaleDateString()}
                  </p>
                </div>
              </div>

              <div class="border-t pt-6">
                <div class="flex justify-between items-center mb-3">
                  <p class="text-xs text-muted">Valid ID</p>
                  <label
                    class="text-xs text-teal hover:text-teal/80 cursor-pointer transition-colors"
                    for="validIdUpload"
                  >
                    {selectedTenant?.valid_id_url ? "Update ID" : "Upload ID"}
                  </label>
                  <input
                    type="file"
                    id="validIdUpload"
                    accept=".jpg,.jpeg,.png,.webp,.pdf"
                    class="hidden"
                    on:change={handleValidIdUpload}
                  />
                </div>

                {#if selectedTenant?.valid_id_url}
                  {#if selectedTenant.valid_id_type
                    ?.toLowerCase()
                    .includes("pdf")}
                    <div
                      class="border rounded-lg p-4 bg-back hover:bg-back/80 transition-colors"
                    >
                      <a
                        href={selectedTenant.valid_id_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center gap-2 text-teal hover:text-teal/80 transition-colors"
                      >
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="20"
                          height="20"
                          viewBox="0 0 24 24"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        >
                          <path
                            d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"
                          />
                          <polyline points="14 2 14 8 20 8" />
                        </svg>
                        <span class="text-sm">View PDF Document</span>
                      </a>
                    </div>
                  {:else}
                    <!-- svelte-ignore a11y_click_events_have_key_events -->
                    <!-- svelte-ignore a11y_no_noninteractive_element_interactions -->
                    <img
                      src={selectedTenant.valid_id_url}
                      alt="Valid ID"
                      class="max-w-full h-auto rounded-lg border cursor-pointer hover:opacity-90 transition-opacity"
                      on:click={() =>
                        window.open(selectedTenant.valid_id_url, "_blank")}
                    />
                  {/if}
                {:else}
                  <p class="text-xs text-muted bg-back p-3 rounded-lg">
                    No valid ID uploaded.
                  </p>
                {/if}
              </div>

              {#if selectedTenant.current_lease}
                <div class="border-t pt-6">
                  <h3 class="text-lg font-bold text-teal mb-4">
                    Lease Information
                  </h3>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="space-y-1">
                      <p class="text-xs text-muted">Start Date</p>
                      <p class="text-sm font-medium text-slate">
                        {new Date(
                          selectedTenant.current_lease.start_date,
                        ).toLocaleDateString()}
                      </p>
                    </div>
                    <div class="space-y-1">
                      <p class="text-xs text-muted">End Date</p>
                      <p class="text-sm font-medium text-slate">
                        {new Date(
                          selectedTenant.current_lease.end_date,
                        ).toLocaleDateString()}
                      </p>
                    </div>
                    <div class="space-y-1">
                      <p class="text-xs text-muted">Monthly Rent</p>
                      <p class="text-sm font-medium text-slate">
                        ₱{selectedTenant.current_lease.rent_amount.toLocaleString()}
                      </p>
                    </div>
                    <div class="space-y-1">
                      <p class="text-xs text-muted">Status</p>
                      <p class="text-sm font-medium">
                        {#if new Date(selectedTenant.current_lease.end_date) <= new Date()}
                          <span class="text-red">Overdue</span>
                        {:else}
                          <span class="text-green">Active</span>
                        {/if}
                      </p>
                    </div>
                  </div>
                </div>
              {/if}
            </div>
          {:else}
            <div class="flex items-center justify-center h-full p-6">
              <p class="text-muted text-sm">
                Select a tenant to view their information
              </p>
            </div>
          {/if}
        </div>
      </div>
    </div>
  </div>
</div>

<TenantFormModal isOpen={showModal} onClose={closeModal} />
