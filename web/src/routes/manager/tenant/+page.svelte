<script lang="ts">
  import { api } from "$lib/services/api";
  import { onMount } from "svelte";
  import swal from "sweetalert2";
  import { load } from "../+layout";

  let tenants: any[] = [];

  let error: string | null = null;
  let success: string | null = null;

  // Add validation state
  let errors = {
    unit_number: "",
    move_in_date: "",
    start_date: "",
    end_date: "",
    rent_amount: "",
    tenants: [] as string[],
  };

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

  function getTodayDate() {
    return new Date().toISOString().split("T")[0];
  }

  let formData = {
    unit_number: "",
    move_in_date: getTodayDate(),
    start_date: getTodayDate(),
    end_date: "",
    rent_amount: "",
    tenants: [
      {
        first_name: "",
        last_name: "",
      },
    ],
  };

  function addTenant() {
    formData.tenants = [...formData.tenants, { first_name: "", last_name: "" }];
  }

  function removeTenant(index: number) {
    formData.tenants = formData.tenants.filter((_, i) => i !== index);
  }

  // Validation function
  async function validateForm(): Promise<boolean> {
    let errors = [];

    // Check unit number
    if (!formData.unit_number.trim()) {
      errors.push("Unit number is required");
    }

    // Check dates
    if (!formData.move_in_date) {
      errors.push("Move-in date is required");
    }

    if (!formData.start_date) {
      errors.push("Lease start date is required");
    }

    if (!formData.end_date) {
      errors.push("Lease end date is required");
    }

    // Validate date order
    if (formData.start_date && formData.end_date) {
      const start = new Date(formData.start_date);
      const end = new Date(formData.end_date);
      if (end <= start) {
        errors.push("Lease end date must be after start date");
      }
    }

    // Check rent amount
    if (!formData.rent_amount) {
      errors.push("Rent amount is required");
    } else if (parseFloat(formData.rent_amount) <= 0) {
      errors.push("Rent amount must be greater than 0");
    }

    // Validate tenants
    if (formData.tenants.length === 0) {
      errors.push("At least one tenant is required");
    }

    formData.tenants.forEach((tenant, index) => {
      if (!tenant.first_name.trim() || !tenant.last_name.trim()) {
        errors.push(
          `Tenant ${index + 1}: Both first and last name are required`,
        );
      }
    });

    if (errors.length > 0) {
      await swal.fire({
        title: "Validation Error",
        html: errors.map((err) => `• ${err}`).join("<br>"),
        icon: "error",
        confirmButtonText: "OK",
      });
      return false;
    }

    return true;
  }

  // Add skipConfirmation parameter
  async function closeModal(skipConfirmation: boolean = false) {
    const hasData = Object.values(formData).some((value) => {
      if (Array.isArray(value)) {
        return (
          value.length > 0 &&
          value.some(
            (tenant) => tenant.first_name.trim() || tenant.last_name.trim(),
          )
        );
      }
      return value !== "";
    });

    if (hasData && !skipConfirmation) {
      const result = await swal.fire({
        title: "Close Form",
        text: "Are you sure you want to close? All entered data will be lost.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, close",
        cancelButtonText: "Cancel",
      });

      if (!result.isConfirmed) {
        return;
      }
    }

    showModal = false;
    formData = {
      unit_number: "",
      move_in_date: getTodayDate(),
      start_date: getTodayDate(),
      end_date: "",
      rent_amount: "",
      tenants: [{ first_name: "", last_name: "" }],
    };
  }

  async function handleSubmit(event: SubmitEvent) {
    event.preventDefault();

    const isValid = await validateForm();
    if (!isValid) return;

    try {
      const response = await api.post("addtenant", formData);

      if (response.status.remarks === "success") {
        await swal.fire({
          title: "Success!",
          text: response.status.message,
          icon: "success",
          confirmButtonText: "OK",
        });
        closeModal(true); // Pass true to skip confirmation
        await loadTenants();
      } else {
        await swal.fire({
          title: "Error",
          text: response.status.message,
          icon: "error",
          confirmButtonText: "OK",
        });
      }
    } catch (err: any) {
      await swal.fire({
        title: "Error",
        text: err.message,
        icon: "error",
        confirmButtonText: "OK",
      });
    }
  }
</script>

<h1 class="text-3xl font-bold text-teal">Tenant Management</h1>
<section class="grid grid-cols-2 gap-8 mt-5">
  <!-- LEFT SIDE -->
  <div class="bg-back rounded-lg p-6">
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
  </div>

  <!-- RIGHT SIDE -->
  <div class="space-y-6">
    <!-- ADD NEW TENANT BUTTON -->
    <div class="bg-back rounded-lg p-6">
      <button
        on:click={openModal}
        class="w-full bg-muted hover:bg-muted/80 text-white py-3 px-4 rounded-lg transition-colors"
      >
        Add New Tenant
      </button>
    </div>

    <!-- PLACEHOLDER -->
    <div class="bg-back rounded-lg p-6 min-h-[300px]">
      <h2 class="text-xl font-bold mb-4">Highlighted Tenants</h2>
      <p class="text-gray-600">(Display of tenants with outstanding rent)</p>
    </div>
  </div>

  <!-- FORM MODAL-->
  {#if showModal}
    <div
      class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center"
    >
      <div
        class="bg-white p-6 rounded-lg shadow-lg max-h-[90vh] overflow-y-auto"
      >
        <h2 class="text-xl font-bold mb-4">Add New Tenant:</h2>

        <form on:submit|preventDefault={handleSubmit}>
          <!-- Unit Information -->
          <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3">Unit Information *</h3>
            <div class="mb-4">
              <label
                class="block text-gray-700 text-sm font-bold mb-2"
                for="unit_number"
              >
                Unit Number:
              </label>
              <input
                type="text"
                id="unit_number"
                bind:value={formData.unit_number}
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              />
            </div>
          </div>

          <!-- Tenant Information Section -->
          <div class="mb-6">
            <div class="flex justify-between items-center mb-3">
              <h3 class="text-lg font-semibold">Tenant Information *</h3>
              <button
                type="button"
                on:click={addTenant}
                class="bg-green-500 hover:bg-green-700 text-green-500 font-bold py-1 px-3 rounded text-sm"
              >
                + Add more People
              </button>
            </div>

            {#each formData.tenants as tenant, index}
              <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="flex justify-between items-center mb-2">
                  <h4 class="font-medium">Tenant {index + 1}</h4>
                  {#if formData.tenants.length > 1}
                    <button
                      type="button"
                      on:click={() => removeTenant(index)}
                      class="bg-red-500 hover:bg-red-700 text-black font-bold rounded text-sm"
                    >
                      Remove
                    </button>
                  {/if}
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div class="mb-4">
                    <p class="block text-gray-700 text-sm font-bold mb-2">
                      First Name
                    </p>
                    <input
                      type="text"
                      bind:value={tenant.first_name}
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    />
                  </div>

                  <div class="mb-4">
                    <p class="block text-gray-700 text-sm font-bold mb-2">
                      Last Name
                    </p>
                    <input
                      type="text"
                      bind:value={tenant.last_name}
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    />
                  </div>
                </div>
              </div>
            {/each}

            <div class="mb-4">
              <p class="block text-gray-700 text-sm font-bold mb-2">
                Move-in Date
              </p>
              <input
                type="date"
                bind:value={formData.move_in_date}
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              />
            </div>
          </div>

          <!-- Lease Information Section -->
          <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3">Lease Information *</h3>
            <div class="grid grid-cols-2 gap-4">
              <div class="mb-4">
                <p class="block text-gray-700 text-sm font-bold mb-2">
                  Lease Start Date
                </p>
                <input
                  type="date"
                  bind:value={formData.start_date}
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                />
              </div>

              <div class="mb-4">
                <p class="block text-gray-700 text-sm font-bold mb-2">
                  Lease End Date
                </p>
                <input
                  type="date"
                  bind:value={formData.end_date}
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                />
              </div>

              <div class="mb-4 col-span-2">
                <p class="block text-gray-700 text-sm font-bold mb-2">
                  Rent Amount
                </p>
                <input
                  type="number"
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
              on:click={() => closeModal(false)}
            >
              Cancel
            </button>
            <button
              type="submit"
              class="bg-blue-500 hover:bg-blue-700 text-black font-bold py-2 px-4 rounded"
            >
              Save
            </button>
          </div>
        </form>
      </div>
    </div>
  {/if}
</section>
